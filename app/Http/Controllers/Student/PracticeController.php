<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\PracticeSession;
use App\Models\QuestionBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PracticeController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->with('currentLevel')->first();

        $pastSessions = $student
            ? PracticeSession::where('student_id', $student->id)
                ->with('level')
                ->latest()
                ->limit(10)
                ->get()
            : collect();

        $levels = Level::where('is_active', true)->orderBy('number')->get();

        return view('student.practice.index', compact('student', 'pastSessions', 'levels'));
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level_id'               => ['required', 'exists:levels,id'],
            'difficulty'             => ['nullable', 'in:easy,medium,hard,all'],
            'count'                  => ['required', 'integer', 'min:5', 'max:300'],
            'session_length_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $student = Auth::user()->student()->firstOrFail();

        $difficulty = $data['difficulty'] ?? null;

        // Questions may be authored against a specific level or globally (level_id null),
        // so include both — mirrors how ExamController::start pulls its question pool.
        $questions = QuestionBank::where('status', 'approved')
            ->where(fn ($q) => $q->where('level_id', $data['level_id'])->orWhereNull('level_id'))
            ->when($difficulty && $difficulty !== 'all', fn($q) => $q->where('difficulty', $difficulty))
            ->inRandomOrder()
            ->limit($data['count'])
            ->get(['id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'difficulty']);

        if ($questions->isEmpty()) {
            return back()
                ->withErrors(['level_id' => 'No questions available for this level and difficulty.'])
                ->withInput();
        }

        $session = PracticeSession::create([
            'student_id'       => $student->id,
            'level_id'         => $data['level_id'],
            'difficulty'       => $difficulty === 'all' ? null : $difficulty,
            'total_questions'  => $questions->count(),
            'duration_minutes' => $data['session_length_minutes'] ?? null,
        ]);

        Cache::put("practice_{$session->id}_questions", $questions->values()->toArray(), now()->addHours(2));
        Cache::put("practice_{$session->id}_index", 0, now()->addHours(2));
        Cache::put("practice_{$session->id}_answers", [], now()->addHours(2));

        return redirect()->route('student.practice.session', $session);
    }

    public function session(PracticeSession $session): View|RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        if ($session->completed_at) {
            return redirect()->route('student.practice.results', $session);
        }

        $questions = Cache::get("practice_{$session->id}_questions", []);
        $index     = Cache::get("practice_{$session->id}_index", 0);

        if (empty($questions) || $index >= count($questions)) {
            return $this->finalize($session, $student);
        }

        $question   = $questions[$index];
        $totalCount = count($questions);
        $answered   = Cache::get("practice_{$session->id}_answers", []);

        return view('student.practice.session', compact('session', 'question', 'index', 'totalCount', 'answered'));
    }

    public function answer(Request $request, PracticeSession $session): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        $data = $request->validate([
            'answer' => ['required', 'in:a,b,c,d'],
        ]);

        $questions = Cache::get("practice_{$session->id}_questions", []);
        $index     = Cache::get("practice_{$session->id}_index", 0);
        $answers   = Cache::get("practice_{$session->id}_answers", []);

        if (isset($questions[$index])) {
            $q = $questions[$index];
            $answers[$index] = [
                'selected'  => $data['answer'],
                'correct'   => strtolower($q['correct_answer']),
                'is_correct'=> strtolower($data['answer']) === strtolower($q['correct_answer']),
            ];
            Cache::put("practice_{$session->id}_answers", $answers, now()->addHours(2));
            Cache::put("practice_{$session->id}_index", $index + 1, now()->addHours(2));
        }

        $nextIndex = $index + 1;

        if ($nextIndex >= count($questions)) {
            return $this->finalize($session, $student);
        }

        return redirect()->route('student.practice.session', $session);
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
            return redirect()->route('student.practice.results', $session);
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

        return redirect()->route('student.practice.results', $session);
    }

    public function results(PracticeSession $session): View
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($session->student_id !== $student->id) {
            abort(403);
        }

        $session->load('level');

        // Avg speed per question (session duration / questions)
        $durationSec    = $session->created_at && $session->completed_at
            ? $session->completed_at->diffInSeconds($session->created_at) : 0;
        $avgSpeed       = $session->total_questions > 0 ? round($durationSec / $session->total_questions, 1) : null;

        // Last 7 days accuracy for chart
        $weekChart = PracticeSession::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays(6)->startOfDay())
            ->orderBy('completed_at')
            ->get()
            ->groupBy(fn($s) => $s->completed_at->format('D'))
            ->map(fn($g) => round($g->avg('accuracy'), 0));

        $chartLabels = collect(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'])
            ->map(fn($d) => ['label' => $d, 'value' => $weekChart->get($d, 0)])
            ->values();

        // Yesterday's accuracy for comparison
        $yesterdayAvg = PracticeSession::where('student_id', $student->id)
            ->whereDate('completed_at', now()->subDay())
            ->avg('accuracy') ?? 0;
        $vsYesterday  = round($session->accuracy - $yesterdayAvg);

        return view('student.practice.results', compact('session', 'avgSpeed', 'chartLabels', 'vsYesterday'));
    }
}
