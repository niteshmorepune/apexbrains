<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\PracticeSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user    = Auth::user();
        $student = $user->student()->with('currentLevel')->first();

        $recentAttempts = $student
            ? $student->examAttempts()
                ->with('exam.level')
                ->latest('submitted_at')
                ->limit(3)
                ->get()
            : collect();

        $practiceThisWeek = $student
            ? PracticeSession::where('student_id', $student->id)
                ->where('created_at', '>=', now()->startOfWeek())
                ->count()
            : 0;

        $upcomingExam = $student
            ? Exam::where('franchise_id', $student->franchise_id)
                ->where('level_id', $student->current_level_id)
                ->where('is_active', true)
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->first()
            : null;

        return view('student.home', compact(
            'student',
            'recentAttempts',
            'practiceThisWeek',
            'upcomingExam'
        ));
    }
}
