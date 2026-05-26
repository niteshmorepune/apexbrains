<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Payment;
use Illuminate\Http\Request;
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
        $franchiseCount = Franchise::where('status', 'active')->count();
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

        // Branch revenue share
        $branchRevenue = Franchise::withSum(['payments as revenue' => function ($q) use ($from, $to) {
            $q->whereBetween('payment_date', [$from, $to]);
        }], 'amount')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        // Commission breakdown
        $commissions = Franchise::with('commissions')
            ->whereHas('commissions', fn($q) => $q->whereMonth('created_at', now()->month))
            ->withSum(['commissions as total_commission' => fn($q) => $q->whereMonth('created_at', now()->month)], 'amount')
            ->orderByDesc('total_commission')
            ->get();

        return view('admin.revenue', compact(
            'from', 'to', 'totalRevenue', 'monthRevenue', 'perFranchiseAvg',
            'monthlyTrend', 'branchRevenue', 'commissions'
        ));
    }
}
