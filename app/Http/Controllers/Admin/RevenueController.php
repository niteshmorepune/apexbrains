<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Franchise;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RevenueController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->filled('from') ? $request->from : now()->startOfYear()->toDateString();
        $to   = $request->filled('to')   ? $request->to   : now()->toDateString();

        $baseQuery = Payment::whereBetween('payment_date', [$from, $to]);

        $totalRevenue   = (clone $baseQuery)->sum('amount');
        $monthRevenue   = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        $lastYearMonth  = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->subYear()->year)
            ->sum('amount');
        $growthRate     = $lastYearMonth > 0
            ? round((($monthRevenue - $lastYearMonth) / $lastYearMonth) * 100, 1)
            : null;
        $franchiseCount  = Franchise::where('status', 'active')->count();
        $perFranchiseAvg = $franchiseCount > 0 ? $monthRevenue / $franchiseCount : 0;

        // Monthly trend (last 12 months)
        $monthlyTrend = Payment::where('payment_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw('YEAR(payment_date) as yr, MONTH(payment_date) as mo, SUM(amount) as total')
            ->groupBy('yr', 'mo')
            ->orderBy('yr')->orderBy('mo')
            ->get()
            ->map(fn($r) => [
                'label' => date('M Y', mktime(0, 0, 0, $r->mo, 1, $r->yr)),
                'total' => (float) $r->total,
            ]);

        // Branch revenue share with student count
        $branchRevenue = Franchise::withSum(['payments as revenue' => function ($q) use ($from, $to) {
            $q->whereBetween('payment_date', [$from, $to]);
        }], 'amount')
            ->withCount('students')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        // Commission settlement status per franchise across the selected period.
        // 'paid' = all monthly commission records paid, 'partial' = some, 'pending' =
        // records exist but none paid, null = no commission calculated for the period.
        $commissionStatus = Commission::whereBetween('month', [$from, $to])
            ->get()
            ->groupBy('franchise_id')
            ->map(function ($records) {
                $paid = $records->where('status', 'paid')->count();
                if ($paid === 0) {
                    return 'pending';
                }
                return $paid === $records->count() ? 'paid' : 'partial';
            });

        $branchRevenue->each(function ($f) use ($commissionStatus) {
            $f->commission_status = $commissionStatus->get($f->id);
        });

        return view('admin.revenue', compact(
            'from', 'to', 'totalRevenue', 'monthRevenue', 'perFranchiseAvg', 'growthRate',
            'monthlyTrend', 'branchRevenue'
        ));
    }

    public function exportPdf(Request $request): Response
    {
        $from = $request->filled('from') ? $request->from : now()->startOfYear()->toDateString();
        $to   = $request->filled('to')   ? $request->to   : now()->toDateString();

        $totalRevenue = Payment::whereBetween('payment_date', [$from, $to])->sum('amount');

        $branchRevenue = Franchise::withSum(['payments as revenue' => function ($q) use ($from, $to) {
            $q->whereBetween('payment_date', [$from, $to]);
        }], 'amount')
            ->orderByDesc('revenue')
            ->get();

        $pdf = Pdf::loadView('admin.pdf.revenue', compact('from', 'to', 'totalRevenue', 'branchRevenue'))
            ->setPaper('a4', 'portrait');

        $filename = 'revenue-report-' . $from . '-to-' . $to . '.pdf';

        return $pdf->download($filename);
    }
}
