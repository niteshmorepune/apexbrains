<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PracticeSession;
use App\Services\RegularQuestionPoolService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PracticeController extends Controller
{
    public function __construct(private RegularQuestionPoolService $pool)
    {
    }

    public function index(): View
    {
        $student = Auth::user()->student()->with('currentLevel')->first();

        $pastSessions = $student
            ? PracticeSession::where('student_id', $student->id)
                ->with(['level', 'category', 'type'])
                ->latest()
                ->limit(10)
                ->get()
            : collect();

        $categories = collect();
        if ($student?->current_level_id) {
            $categories = $this->pool->accessibleCategories($student->current_level_id)
                ->map(function ($category) use ($student) {
                    $category->setRelation('types', $this->pool->accessibleTypes($student->current_level_id, $category->id));

                    return $category;
                });
        }

        return view('student.practice.index', compact('student', 'pastSessions', 'categories'));
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:regular_question_categories,id'],
            'type_id' => ['required', 'exists:regular_question_types,id'],
            'count' => ['required', 'in:10,20,30'],
        ]);

        $student = Auth::user()->student()->firstOrFail();

        if (! $student->current_level_id || ! $this->pool->hasAccess($student->current_level_id, (int) $data['type_id'])) {
            return back()
                ->withErrors(['type_id' => 'This type is not available for your level.'])
                ->withInput();
        }

        $questions = $this->pool->randomFor(
            (int) $data['category_id'],
            (int) $data['type_id'],
            (int) $data['count'],
            ['id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer']
        );

        if ($questions->isEmpty()) {
            return back()
                ->withErrors(['type_id' => 'No questions available for this type yet.'])
                ->withInput();
        }

        $session = PracticeSession::create([
            'student_id' => $student->id,
            'level_id' => $student->current_level_id,
            'category_id' => $data['category_id'],
            'type_id' => $data['type_id'],
            'total_questions' => $questions->count(),
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
        $answered  = Cache::get("practice_{$session->id}_answers", []);

        // Finalize if we've run past the last question OR every question has
        // already been answered (guards against a stale index pointer leaving
        // a fully-answered session stuck on a question view).
        if (empty($questions) || $index >= count($questions) || count($answered) >= count($questions)) {
            return $this->finalize($session, $student);
        }

        $question   = $questions[$index];
        $totalCount = count($questions);

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

        $session->load('level', 'category', 'type');

        // Avg speed per question (session duration / questions)
        $durationSec    = $session->created_at && $session->completed_at
            ? $session->completed_at->diffInSeconds($session->created_at, true) : 0;
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
