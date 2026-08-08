<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\ApexNotification;
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

        $unreadNotifications = $student
            ? ApexNotification::where('franchise_id', $student->franchise_id)
                ->where(function ($q) use ($student) {
                    $q->whereNull('student_id')->orWhere('student_id', $student->id);
                })
                ->where('is_read', false)
                ->count()
            : 0;

        return view('external.home', compact('student', 'recentAttempts', 'unreadNotifications'));
    }
}
