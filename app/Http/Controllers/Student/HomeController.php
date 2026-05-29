<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\PracticeSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user    = Auth::user();
        $student = $user->student()->with(['currentLevel', 'franchise'])->first();

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

        // Daily streak: count consecutive days with any practice session
        $streak = 0;
        if ($student) {
            $day = now()->startOfDay();
            while (true) {
                $hasActivity = PracticeSession::where('student_id', $student->id)
                    ->whereDate('created_at', $day)
                    ->exists();
                if (!$hasActivity) break;
                $streak++;
                $day = $day->copy()->subDay();
                if ($streak >= 365) break;
            }
        }

        // Level progress: average exam score at current level (0–100)
        $levelProgress = 0;
        if ($student?->current_level_id) {
            $avg = ExamAttempt::where('student_id', $student->id)
                ->whereHas('exam', fn($q) => $q->where('level_id', $student->current_level_id))
                ->where('status', 'submitted')
                ->avg('percentage');
            $levelProgress = $avg ? (int) round($avg) : 0;
        }

        // Best streak ever
        $bestStreak = $student
            ? PracticeSession::where('student_id', $student->id)
                ->selectRaw('DATE(created_at) as practice_date')
                ->distinct()
                ->orderBy('practice_date')
                ->pluck('practice_date')
                ->pipe(function ($dates) {
                    $best = $current = 0;
                    $prev = null;
                    foreach ($dates as $date) {
                        if ($prev && \Carbon\Carbon::parse($date)->diffInDays(\Carbon\Carbon::parse($prev)) === 1) {
                            $current++;
                        } else {
                            $current = 1;
                        }
                        $best = max($best, $current);
                        $prev = $date;
                    }
                    return $best;
                })
            : 0;

        return view('student.home', compact(
            'student',
            'recentAttempts',
            'practiceThisWeek',
            'upcomingExam',
            'streak',
            'bestStreak',
            'levelProgress'
        ));
    }
}
