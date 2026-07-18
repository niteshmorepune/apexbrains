<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPracticeAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->first();

        $recentAttempts = $student
            ? CompetitionPracticeAttempt::where('student_id', $student->id)
                ->whereNotNull('submitted_at')
                ->with('level')
                ->latest('submitted_at')
                ->limit(3)
                ->get()
            : collect();

        return view('external.home', compact('student', 'recentAttempts'));
    }
}
