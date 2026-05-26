<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::with(['currentLevel', 'examAttempts'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('last_name', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('level')) {
            $query->where('current_level_id', $request->level);
        }

        $sort = $request->get('sort', 'name');
        $students = $query->get()->map(function ($s) {
            $s->avg_score   = $s->examAttempts->avg('percentage') ?? 0;
            $s->exam_count  = $s->examAttempts->count();
            $s->last_score  = $s->examAttempts->sortByDesc('submitted_at')->first()?->percentage ?? 0;
            return $s;
        })->sortByDesc($sort === 'best_score' ? 'avg_score' : 'first_name');

        return view('franchise.reports.index', compact('students'));
    }

    public function show(Student $student): View
    {
        $student->load('currentLevel', 'examAttempts.exam');

        $attempts = $student->examAttempts
            ->where('status', 'submitted')
            ->sortByDesc('submitted_at')
            ->values();

        $chartData = $attempts->map(fn($a) => [
            'label' => $a->submitted_at?->format('d M'),
            'score' => (float) ($a->percentage ?? 0),
        ])->values();

        return view('franchise.reports.show', compact('student', 'attempts', 'chartData'));
    }
}
