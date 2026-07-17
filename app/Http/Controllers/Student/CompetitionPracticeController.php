<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPracticeAttempt;
use App\Models\CompetitionPracticeConfig;
use App\Models\CompetitionPracticeLevel;
use App\Models\CompetitionQuestionBank;
use App\Services\CompetitionPracticeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Competition Practice is fully automatic per the client's flow doc: no
 * paper browsing, no category/type/count picking — the student's level
 * determines the whole question set via competition_practice_configs.
 */
class CompetitionPracticeController extends Controller
{
    public function __construct(private CompetitionPracticeGenerator $generator)
    {
    }

    public function index(): View
    {
        $student = Auth::user()->student()->with('currentLevel')->firstOrFail();

        $totalQuestions = $student->current_level_id
            ? CompetitionPracticeConfig::where('level_id', $student->current_level_id)->sum('question_count')
            : 0;

        $durationMinutes = $student->current_level_id
            ? (CompetitionPracticeLevel::where('level_id', $student->current_level_id)->value('duration_minutes') ?? 10)
            : 10;

        $pastAttempts = CompetitionPracticeAttempt::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->with('level')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        return view('student.competitions.practice.index', compact('student', 'totalQuestions', 'durationMinutes', 'pastAttempts'));
    }

    public function start(Request $request): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if (! $student->current_level_id) {
            return back()->with('error', 'Your level has not been set yet. Please contact your branch.');
        }

        $questions = $this->generator->generateForLevel($student->current_level_id);

        if ($questions->isEmpty()) {
            return back()->with('error', 'No practice questions are ready for your level yet. Please contact your branch.');
        }

        $attempt = CompetitionPracticeAttempt::create([
            'level_id' => $student->current_level_id,
            'question_ids' => $questions->pluck('id')->values()->all(),
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Cache::put("cp_attempt_{$attempt->id}_answers", [], now()->addHours(3));

        return redirect()->route('student.competitions.practice.attempt', $attempt);
    }

    public function attempt(CompetitionPracticeAttempt $attempt): View|RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();
        abort_unless($attempt->student_id === $student->id, 403);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.competitions.practice.result', $attempt);
        }

        $questionIds = $attempt->question_ids ?? [];

        $questions = CompetitionQuestionBank::whereIn('id', $questionIds)
            ->get(['id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d'])
            ->sortBy(fn ($q) => array_search($q->id, $questionIds))
            ->values();

        $savedAnswers = Cache::get("cp_attempt_{$attempt->id}_answers", []);

        $durationMinutes = CompetitionPracticeLevel::where('level_id', $attempt->level_id)->value('duration_minutes') ?? 10;
        $durationSeconds = $durationMinutes * 60;
        $elapsed = (int) now()->diffInSeconds($attempt->started_at, true);
        $remaining = max(0, $durationSeconds - $elapsed);

        if ($remaining === 0) {
            return $this->doSubmit($attempt, $questions);
        }

        return view('student.competitions.practice.attempt', compact(
            'attempt', 'questions', 'savedAnswers', 'elapsed', 'remaining'
        ));
    }

    public function saveAnswer(Request $request, CompetitionPracticeAttempt $attempt): JsonResponse
    {
        $student = Auth::user()->student()->firstOrFail();
        abort_unless($attempt->student_id === $student->id, 403);

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'selected_answer' => ['required', 'in:a,b,c,d'],
        ]);

        $answers = Cache::get("cp_attempt_{$attempt->id}_answers", []);
        $answers[$data['question_id']] = $data['selected_answer'];
        Cache::put("cp_attempt_{$attempt->id}_answers", $answers, now()->addHours(3));

        return response()->json(['saved' => true]);
    }

    public function submit(Request $request, CompetitionPracticeAttempt $attempt): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();
        abort_unless($attempt->student_id === $student->id, 403);

        $questions = CompetitionQuestionBank::whereIn('id', $attempt->question_ids ?? [])->get(['id', 'correct_answer']);

        return $this->doSubmit($attempt, $questions);
    }

    private function doSubmit(CompetitionPracticeAttempt $attempt, $questions): RedirectResponse
    {
        if ($attempt->status === 'submitted') {
            return redirect()->route('student.competitions.practice.result', $attempt);
        }

        $answers = Cache::get("cp_attempt_{$attempt->id}_answers", []);
        $correct = 0;

        foreach ($questions as $q) {
            if (isset($answers[$q->id]) && strtolower($answers[$q->id]) === strtolower($q->correct_answer)) {
                $correct++;
            }
        }

        $total = count($attempt->question_ids ?? []);
        $pct = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        $attempt->update([
            'score' => $correct,
            'percentage' => $pct,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Cache::forget("cp_attempt_{$attempt->id}_answers");

        return redirect()->route('student.competitions.practice.result', $attempt);
    }

    public function result(CompetitionPracticeAttempt $attempt): View
    {
        $student = Auth::user()->student()->firstOrFail();
        abort_unless($attempt->student_id === $student->id, 403);

        $attempt->load('level');

        $questions = CompetitionQuestionBank::whereIn('id', $attempt->question_ids ?? [])->get();

        return view('student.competitions.practice.result', compact('attempt', 'questions'));
    }
}
