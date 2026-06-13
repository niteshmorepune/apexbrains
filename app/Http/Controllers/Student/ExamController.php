<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\QuestionBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->with('currentLevel')->firstOrFail();

        $upcomingExams = Exam::where(function ($q) use ($student) {
                // Global (Admin-authored) exams plus any legacy franchise exams.
                $q->whereNull('franchise_id')
                  ->orWhere('franchise_id', $student->franchise_id);
            })
            ->where('is_active', true)
            ->where(function ($q) use ($student) {
                $q->whereNull('level_id')
                  ->orWhere('level_id', $student->current_level_id);
            })
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderBy('scheduled_at')
            ->get();

        $pastAttempts = $student->examAttempts()
            ->with('exam.level')
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        return view('student.exams.index', compact('student', 'upcomingExams', 'pastAttempts'));
    }

    public function results(): View
    {
        $student = Auth::user()->student()->with('currentLevel')->firstOrFail();

        $attempts = $student->examAttempts()
            ->with('exam.level')
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->paginate(20);

        $avgScore = $student->examAttempts()->whereNotNull('score')->avg('percentage') ?? 0;
        $totalExams = $student->examAttempts()->whereNotNull('submitted_at')->count();
        $passed = $student->examAttempts()->where('is_passed', true)->count();

        return view('student.results', compact('student', 'attempts', 'avgScore', 'totalExams', 'passed'));
    }

    public function show(Exam $exam): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $canAttempt = $exam->is_active
            && (is_null($exam->scheduled_at) || $exam->scheduled_at->lte(now()))
            && (is_null($exam->max_attempts) || $attempts->count() < $exam->max_attempts)
            && (is_null($exam->expires_at) || $exam->expires_at->isFuture());

        $inProgress = $attempts->where('status', 'in_progress')->first();

        return view('student.exams.show', compact('exam', 'attempts', 'canAttempt', 'inProgress'));
    }

    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attemptCount = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->count();

        if ($exam->scheduled_at && $exam->scheduled_at->isFuture()) {
            return back()->with('error', 'This exam has not started yet. It opens on ' . $exam->scheduled_at->format('d M Y \a\t g:i A') . '.');
        }

        if ($exam->max_attempts && $attemptCount >= $exam->max_attempts) {
            return back()->with('error', 'Maximum attempts reached for this exam.');
        }

        $questions = QuestionBank::where('status', 'approved')
            ->where(fn ($q) => $q->where('level_id', $exam->level_id)->orWhereNull('level_id'))
            ->inRandomOrder()
            ->limit($exam->total_questions)
            ->pluck('id')
            ->toArray();

        $attempt = ExamAttempt::create([
            'exam_id'        => $exam->id,
            'student_id'     => $student->id,
            'franchise_id'   => $student->franchise_id,
            'attempt_number' => $attemptCount + 1,
            'question_ids'   => $questions,
            'started_at'     => now(),
            'status'         => 'in_progress',
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'tab_switch_count'   => 0,
            'fullscreen_exit_count' => 0,
        ]);

        return redirect()->route('student.exams.attempt', $exam);
    }

    public function attempt(Exam $exam): View|RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        $questionIds = $attempt->question_ids ?? [];

        $questions = QuestionBank::whereIn('id', $questionIds)
            ->get(['id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d'])
            ->sortBy(fn($q) => array_search($q->id, $questionIds))
            ->values();

        $savedAnswers = ExamAnswer::where('exam_attempt_id', $attempt->id)
            ->pluck('selected_answer', 'question_id')
            ->toArray();

        $durationSeconds = $exam->duration_minutes * 60;
        $elapsed = now()->diffInSeconds($attempt->started_at);
        $remaining = max(0, $durationSeconds - $elapsed);

        if ($remaining === 0) {
            return $this->doSubmit($attempt, $exam, $student);
        }

        return view('student.exams.attempt', compact(
            'exam', 'attempt', 'questions', 'savedAnswers', 'remaining'
        ));
    }

    public function saveAnswer(Request $request, Exam $exam): JsonResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        $data = $request->validate([
            'question_id'     => ['required', 'exists:question_banks,id'],
            'selected_answer' => ['required', 'in:a,b,c,d'],
            'tab_switches'    => ['nullable', 'integer', 'min:0'],
        ]);

        $question = QuestionBank::findOrFail($data['question_id']);
        $isCorrect = strtolower($data['selected_answer']) === strtolower($question->correct_answer);

        ExamAnswer::updateOrCreate(
            ['exam_attempt_id' => $attempt->id, 'question_id' => $data['question_id']],
            [
                'selected_answer' => $data['selected_answer'],
                'is_correct'      => $isCorrect,
                'answered_at'     => now(),
            ]
        );

        if (isset($data['tab_switches'])) {
            $attempt->update(['tab_switch_count' => $data['tab_switches']]);
        }

        return response()->json(['saved' => true]);
    }

    public function submit(Request $request, Exam $exam): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        if (isset($request->tab_switches)) {
            $attempt->update(['tab_switch_count' => (int) $request->tab_switches]);
        }

        return $this->doSubmit($attempt, $exam, $student);
    }

    private function doSubmit(ExamAttempt $attempt, Exam $exam, $student): RedirectResponse
    {
        $answers  = $attempt->answers()->get();
        $correct  = $answers->where('is_correct', true)->count();
        $total    = count($attempt->question_ids ?? []);
        $score    = $correct;
        $pct      = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
        $passed   = $pct >= $exam->pass_percentage;

        $attempt->update([
            'score'        => $score,
            'percentage'   => $pct,
            'is_passed'    => $passed,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('student.exams.result', $exam);
    }

    public function result(Exam $exam): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->with('answers.question')
            ->firstOrFail();

        // Class average for this exam
        $classAvg = ExamAttempt::where('exam_id', $exam->id)
            ->where('status', 'submitted')
            ->whereNotNull('percentage')
            ->avg('percentage');

        // Time taken
        $timeTaken = $attempt->started_at && $attempt->submitted_at
            ? $attempt->submitted_at->diffInSeconds($attempt->started_at) : 0;

        // Skipped = questions with no answer submitted
        $answeredCount = $attempt->answers->count();
        $totalQ        = count($attempt->question_ids ?? []);
        $skipped       = max(0, $totalQ - $answeredCount);

        return view('student.exams.result', compact('exam', 'attempt', 'classAvg', 'timeTaken', 'skipped'));
    }
}
