<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentLevel;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalStudents   = Student::count();
        $activeFranchises = Franchise::where('status', 'active')->count();
        $pendingFranchises = Franchise::where('status', 'pending')->count();

        $monthlyRevenue = Payment::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->where('status', 'paid')
            ->sum('amount');

        $prevRevenue = Payment::whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->where('status', 'paid')
            ->sum('amount');

        $revenueGrowth = $prevRevenue > 0
            ? round((($monthlyRevenue - $prevRevenue) / $prevRevenue) * 100)
            : 0;

        $avgScore = ExamAttempt::whereNotNull('score')->avg('score') ?? 0;

        // Monthly revenue for last 12 months
        $monthlyTrend = Payment::where('status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw('YEAR(paid_at) as year, MONTH(paid_at) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'label' => date('M', mktime(0, 0, 0, $r->month, 1)),
                'total' => (float) $r->total,
            ]);

        // Student count by level
        $levelDistribution = StudentLevel::select('level_id', DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->with('level:id,title,sort_order')
            ->groupBy('level_id')
            ->orderBy('level_id')
            ->get()
            ->map(fn($r) => [
                'label' => $r->level->title ?? "Level {$r->level_id}",
                'total' => $r->total,
            ]);

        // Franchise overview table
        $franchises = Franchise::withCount(['students'])
            ->select('franchises.*')
            ->selectSub(
                Payment::whereColumn('franchise_id', 'franchises.id')
                    ->whereMonth('paid_at', now()->month)
                    ->where('status', 'paid')
                    ->selectRaw('COALESCE(SUM(amount), 0)'),
                'monthly_revenue'
            )
            ->selectSub(
                ExamAttempt::whereHas('exam', fn($q) => $q->whereColumn('franchise_id', 'franchises.id'))
                    ->whereNotNull('score')
                    ->selectRaw('COALESCE(AVG(score), 0)'),
                'avg_score'
            )
            ->orderByDesc('students_count')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents', 'activeFranchises', 'pendingFranchises',
            'monthlyRevenue', 'revenueGrowth', 'avgScore',
            'monthlyTrend', 'levelDistribution', 'franchises'
        ));
    }
}
