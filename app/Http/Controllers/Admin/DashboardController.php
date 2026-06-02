<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Payment;
use App\Models\Student;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalStudents     = Student::count();
        $lastMonthStudents = Student::whereMonth('enrollment_date', now()->subMonth()->month)
            ->whereYear('enrollment_date', now()->subMonth()->year)->count();
        $studentGrowth     = $lastMonthStudents > 0
            ? round((Student::whereMonth('enrollment_date', now()->month)->whereYear('enrollment_date', now()->year)->count() - $lastMonthStudents) / $lastMonthStudents * 100)
            : 0;
        $activeFranchises  = Franchise::where('status', 'active')->count();
        $franchiseGrowth   = Franchise::where('status', 'active')
            ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $pendingFranchises = Franchise::where('status', 'pending')->count();

        $monthlyRevenue = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $prevRevenue = Payment::whereMonth('payment_date', now()->subMonth()->month)
            ->whereYear('payment_date', now()->subMonth()->year)
            ->sum('amount');

        $revenueGrowth = $prevRevenue > 0
            ? round((($monthlyRevenue - $prevRevenue) / $prevRevenue) * 100)
            : 0;

        $avgScore = ExamAttempt::whereNotNull('score')->avg('score') ?? 0;

        // Monthly revenue for last 12 months
        $monthlyTrend = Payment::where('payment_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get()
            ->map(fn($r) => [
                'label' => date('M', mktime(0, 0, 0, $r->month, 1)),
                'total' => (float) $r->total,
            ]);

        // Student count by level (based on each student's current level)
        $levelDistribution = Student::select('current_level_id', DB::raw('count(*) as total'))
            ->whereNotNull('current_level_id')
            ->with('currentLevel:id,title,sort_order')
            ->groupBy('current_level_id')
            ->get()
            ->sortBy(fn($r) => $r->currentLevel->sort_order ?? PHP_INT_MAX)
            ->values()
            ->map(fn($r) => [
                'label' => $r->currentLevel->title ?? "Level {$r->current_level_id}",
                'total' => $r->total,
            ]);

        // Franchise overview table
        $franchises = Franchise::select('franchises.*')
            ->selectSub(
                Student::whereColumn('franchise_id', 'franchises.id')->selectRaw('COUNT(*)'),
                'students_count'
            )
            ->selectSub(
                Payment::whereColumn('franchise_id', 'franchises.id')
                    ->whereMonth('payment_date', now()->month)
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
            'studentGrowth', 'franchiseGrowth',
            'monthlyRevenue', 'revenueGrowth', 'avgScore',
            'monthlyTrend', 'levelDistribution', 'franchises'
        ));
    }

    public function export(): Response
    {
        $franchises = Franchise::select('franchises.*')
            ->selectSub(
                Student::whereColumn('franchise_id', 'franchises.id')->selectRaw('COUNT(*)'),
                'students_count'
            )
            ->selectSub(
                Payment::whereColumn('franchise_id', 'franchises.id')
                    ->whereMonth('payment_date', now()->month)
                    ->selectRaw('COALESCE(SUM(amount), 0)'),
                'monthly_revenue'
            )
            ->orderByDesc('students_count')
            ->get();

        $csv  = "Franchise,City,Status,Students,Monthly Revenue (₹),Commission Rate\n";
        foreach ($franchises as $f) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $f->name) . '"',
                '"' . $f->city . '"',
                $f->status,
                $f->students_count,
                number_format($f->monthly_revenue, 2, '.', ''),
                $f->commission_rate,
            ]) . "\n";
        }

        $filename = 'apex-brains-dashboard-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
