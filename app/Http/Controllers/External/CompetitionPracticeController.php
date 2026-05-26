<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPracticeAttempt;
use App\Models\CompetitionPracticePaper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CompetitionPracticeController extends Controller
{
    public function index(): View
    {
        $papers = CompetitionPracticePaper::where('is_active', true)
            ->orderBy('paper_number')
            ->get();

        $student = Auth::user()->student()->first();

        $attemptMap = $student
            ? CompetitionPracticeAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->orderByDesc('submitted_at')
                ->get(['paper_id', 'percentage'])
                ->keyBy('paper_id')
            : collect();

        return view('external.practice.index', compact('papers', 'attemptMap'));
    }

    public function start(Request $request, CompetitionPracticePaper $paper): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionPracticeAttempt::create([
            'paper_id'   => $paper->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status'     => 'in_progress',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Cache::put("ext_cp_{$attempt->id}_answers", [], now()->addHours(3));

        return redirect()->route('external.practice.attempt', $paper);
    }

    public function attempt(CompetitionPracticePaper $paper): View|RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionPracticeAttempt::where('paper_id', $paper->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        $questions = $paper->paperQuestions()
            ->with('question:id,question_text,option_a,option_b,option_c,option_d')
            ->orderBy('sort_order')
            ->get();

        $savedAnswers = Cache::get("ext_cp_{$attempt->id}_answers", []);

        $remaining = max(0, ($paper->duration_minutes * 60) - now()->diffInSeconds($attempt->started_at));

        if ($remaining === 0) {
            return $this->doSubmit($attempt, $paper, $questions);
        }

        return view('external.practice.attempt', compact('paper', 'attempt', 'questions', 'savedAnswers', 'remaining'));
    }

    public function saveAnswer(Request $request, CompetitionPracticePaper $paper): JsonResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionPracticeAttempt::where('paper_id', $paper->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        $data = $request->validate([
            'question_id'     => ['required', 'integer'],
            'selected_answer' => ['required', 'in:a,b,c,d'],
        ]);

        $answers = Cache::get("ext_cp_{$attempt->id}_answers", []);
        $answers[$data['question_id']] = $data['selected_answer'];
        Cache::put("ext_cp_{$attempt->id}_answers", $answers, now()->addHours(3));

        return response()->json(['saved' => true]);
    }

    public function submit(Request $request, CompetitionPracticePaper $paper): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionPracticeAttempt::where('paper_id', $paper->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        $questions = $paper->paperQuestions()->with('question')->orderBy('sort_order')->get();

        return $this->doSubmit($attempt, $paper, $questions);
    }

    private function doSubmit(CompetitionPracticeAttempt $attempt, CompetitionPracticePaper $paper, $questions): RedirectResponse
    {
        if ($attempt->status === 'completed') {
            return redirect()->route('external.practice.result', $paper);
        }

        $answers = Cache::get("ext_cp_{$attempt->id}_answers", []);
        $correct = 0;

        foreach ($questions as $pq) {
            $q = $pq->question;
            if (isset($answers[$q->id]) && strtolower($answers[$q->id]) === strtolower($q->correct_answer)) {
                $correct++;
            }
        }

        $total = $questions->count();
        $pct   = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        $attempt->update([
            'score'        => $correct,
            'percentage'   => $pct,
            'status'       => 'completed',
            'submitted_at' => now(),
        ]);

        Cache::forget("ext_cp_{$attempt->id}_answers");

        return redirect()->route('external.practice.result', $paper);
    }

    public function result(CompetitionPracticePaper $paper): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionPracticeAttempt::where('paper_id', $paper->id)
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest('submitted_at')
            ->firstOrFail();

        $questions = $paper->paperQuestions()->with('question')->orderBy('sort_order')->get();

        return view('external.practice.result', compact('paper', 'attempt', 'questions'));
    }
}
