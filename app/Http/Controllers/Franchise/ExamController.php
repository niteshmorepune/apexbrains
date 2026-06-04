<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Read-only. Exams are authored centrally by Admin; the franchise only monitors
 * its own students' attempts. The Exam model's ExamTenantScope already limits
 * visible exams to global (Admin) exams plus any legacy franchise-owned ones.
 */
class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with('level')
            ->latest()
            ->paginate(20);

        return view('franchise.exams.index', compact('exams'));
    }

    public function show(Exam $exam): View
    {
        $exam->load('level');

        $franchiseId = Auth::user()->franchise_id;

        // Restrict monitoring stats to this franchise's own students.
        $attempts = $exam->attempts()->where('franchise_id', $franchiseId);

        $attemptCount = (clone $attempts)->count();
        $passCount    = (clone $attempts)->where('is_passed', true)->count();
        $avgScore     = (clone $attempts)->whereNotNull('percentage')->avg('percentage');

        $recentAttempts = (clone $attempts)
            ->with('student')
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        return view('franchise.exams.show', compact('exam', 'attemptCount', 'passCount', 'avgScore', 'recentAttempts'));
    }
}
