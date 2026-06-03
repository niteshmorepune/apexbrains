<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Exam;
use App\Models\Fee;
use App\Models\Level;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $franchiseId = Auth::user()->franchise_id;

        $totalStudents   = Student::where('is_active', true)->count();
        $newThisMonth    = Student::where('is_active', true)
            ->whereMonth('enrollment_date', now()->month)
            ->whereYear('enrollment_date', now()->year)->count();
        $pendingFees     = Fee::where('status', '!=', 'paid')->count();
        $promotionsDue   = \App\Models\ExamAttempt::whereHas('student', fn($q) => $q->where('franchise_id', $franchiseId))
            ->where('status', 'submitted')
            ->whereRaw('percentage >= 80')
            ->distinct('student_id')->count('student_id');

        // Monthly revenue (kept for fee context)
        $monthRevenue = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // Students by level group (pairs L1-2, L3-4, ...)
        $levels = Level::orderBy('number')->get();
        $byLevel = $levels->groupBy(fn($l) => 'L' . (ceil($l->number / 2) * 2 - 1) . '-' . (ceil($l->number / 2) * 2))
            ->map(fn($grp) => Student::where('is_active', true)
                ->whereIn('current_level_id', $grp->pluck('id'))
                ->count()
            );

        // Recent activity from audit log
        $recentActivity = AuditLog::where('franchise_id', $franchiseId)
            ->latest('created_at')
            ->limit(6)
            ->get();

        // Student overview table (top 8 by enrollment date, with last exam score + latest fee)
        $students = Student::with([
                'currentLevel',
                'examAttempts' => fn($q) => $q->latest()->limit(1),
                'fees'         => fn($q) => $q->latest()->limit(1),
            ])
            ->where('is_active', true)
            ->latest('enrollment_date')
            ->limit(8)
            ->get();

        return view('franchise.dashboard', compact(
            'totalStudents', 'newThisMonth', 'pendingFees', 'promotionsDue',
            'monthRevenue', 'byLevel', 'recentActivity', 'students'
        ));
    }
}
