<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\PracticeSession;
use App\Services\CompetitionQuestionPoolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * External competition practice. External students have no curriculum level,
 * so questions are drawn randomly from the Competition Question Bank (across
 * all categories/types) by difficulty — matches CLAUDE.md's framing of
 * External as the "competition-only" portal.
 */
class PracticeController extends Controller
{
    public function __construct(private CompetitionQuestionPoolService $pool)
    {
    }

    public function index(): View
    {
        $student = Auth::user()->student()->first();

        $pastSessions = $student
            ? PracticeSession::where('student_id', $student->id)
                ->latest()
                ->limit(10)
                ->get()
            : collect();

        return view('external.practice.index', compact('student', 'pastSessions'));
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'difficulty' => ['nullable', 'in:easy,medium,hard,all'],
            'count'      => ['required', 'integer', 'min:5', 'max:100'],
        ]);

        $student    = Auth::user()->student()->firstOrFail();
        $difficulty = $data['difficulty'] ?? null;

        $questions = $this->pool->randomAny(
            $data['count'],
            $difficulty,
            ['id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'difficulty']
        );

        if ($questions->isEmpty()) {
            return back()->withErrors(['difficulty' => 'No questions available for this difficulty yet.']);
        }

        // practice_sessions.level_id is NOT NULL; external students have no level,
        // so record the session against the first curriculum level as a placeholder.
        $placeholderLevel = Level::orderBy('number')->value('id');

        $session = PracticeSession::create([
            'student_id'       => $student->id,
            'level_id'         => $placeholderLevel,
            'difficulty'       => $difficulty === 'all' ? null : $difficulty,
            'total_questions'  => $questions->count(),
            'duration_minutes' => 10,
        ]);

        Cache::put("practice_{$session->id}_questions", $questions->values()->toArray(), now()->addHours(2));
        Cache::put("practice_{$session->id}_index", 0, now()->addHours(2));
        Cache::put("practice_{$session->id}_answers", [], now()->addHours(2));

        return redirect()->route('external.practice.session', $session);
    }

    public function session(PracticeSession $session): View|RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        if ($session->completed_at) {
            return redirect()->route('external.practice.results', $session);
        }

        $questions = Cache::get("practice_{$session->id}_questions", []);
        $index     = Cache::get("practice_{$session->id}_index", 0);
        $answered  = Cache::get("practice_{$session->id}_answers", []);

        if (empty($questions) || $index >= count($questions) || count($answered) >= count($questions)) {
            return $this->finalize($session, $student);
        }

        $question   = $questions[$index];
        $totalCount = count($questions);

        return view('external.practice.session', compact('session', 'question', 'index', 'totalCount', 'answered'));
    }

    public function answer(Request $request, PracticeSession $session): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        $data = $request->validate(['answer' => ['required', 'in:a,b,c,d']]);

        $questions = Cache::get("practice_{$session->id}_questions", []);
        $index     = Cache::get("practice_{$session->id}_index", 0);
        $answers   = Cache::get("practice_{$session->id}_answers", []);

        if (isset($questions[$index])) {
            $q = $questions[$index];
            $answers[$index] = [
                'selected'   => $data['answer'],
                'correct'    => strtolower($q['correct_answer']),
                'is_correct' => strtolower($data['answer']) === strtolower($q['correct_answer']),
            ];
            Cache::put("practice_{$session->id}_answers", $answers, now()->addHours(2));
            Cache::put("practice_{$session->id}_index", $index + 1, now()->addHours(2));
        }

        if ($index + 1 >= count($questions)) {
            return $this->finalize($session, $student);
        }

        return redirect()->route('external.practice.session', $session);
    }

    public function submit(Request $request, PracticeSession $session): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        return $this->finalize($session, $student);
    }

    private function finalize(PracticeSession $session, $student): RedirectResponse
    {
        if ($session->completed_at) {
            return redirect()->route('external.practice.results', $session);
        }

        $questions = Cache::get("practice_{$session->id}_questions", []);
        $answers   = Cache::get("practice_{$session->id}_answers", []);

        $correct  = collect($answers)->where('is_correct', true)->count();
        $total    = count($questions);
        $accuracy = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        $session->update([
            'questions_correct' => $correct,
            'accuracy'          => $accuracy,
            'completed_at'      => now(),
        ]);

        Cache::forget("practice_{$session->id}_questions");
        Cache::forget("practice_{$session->id}_index");
        Cache::forget("practice_{$session->id}_answers");

        return redirect()->route('external.practice.results', $session);
    }

    public function results(PracticeSession $session): View
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        $durationSec = $session->created_at && $session->completed_at
            ? $session->completed_at->diffInSeconds($session->created_at, true) : 0;
        $avgSpeed = $session->total_questions > 0 ? round($durationSec / $session->total_questions, 1) : null;

        return view('external.practice.results', compact('session', 'avgSpeed'));
    }

    public function history(): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $sessions = PracticeSession::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->paginate(20);

        $avgScore  = (float) (PracticeSession::where('student_id', $student->id)->whereNotNull('completed_at')->avg('accuracy') ?? 0);
        $totalDone = PracticeSession::where('student_id', $student->id)->whereNotNull('completed_at')->count();

        return view('external.results', compact('student', 'sessions', 'avgScore', 'totalDone'));
    }
}
