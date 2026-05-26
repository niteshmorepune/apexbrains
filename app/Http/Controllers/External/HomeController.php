<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPracticeAttempt;
use App\Models\CompetitionPracticePaper;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $student     = Auth::user()->student()->first();
        $totalPapers = CompetitionPracticePaper::where('is_active', true)->count();

        $attemptedCount = $student
            ? CompetitionPracticeAttempt::where('student_id', $student->id)->count()
            : 0;

        $recentAttempts = $student
            ? CompetitionPracticeAttempt::where('student_id', $student->id)
                ->with('paper')
                ->whereNotNull('submitted_at')
                ->latest('submitted_at')
                ->limit(3)
                ->get()
            : collect();

        return view('external.home', compact('student', 'totalPapers', 'attemptedCount', 'recentAttempts'));
    }
}
