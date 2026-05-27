<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Franchise;
use App\Models\Payment;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->filled('month') ? $request->month : now()->format('Y-m');
        [$year, $mo] = explode('-', $month);

        $monthDate = $year . '-' . str_pad($mo, 2, '0', STR_PAD_LEFT) . '-01';

        // Load all commission records for this month keyed by franchise_id
        $commissionRecords = Commission::where('month', $monthDate)
            ->get()
            ->keyBy('franchise_id');

        $franchises = Franchise::where('status', 'active')
            ->withSum(['payments as gross_revenue' => function ($q) use ($year, $mo) {
                $q->whereYear('payment_date', $year)
                  ->whereMonth('payment_date', $mo);
            }], 'amount')
            ->get()
            ->map(function ($f) use ($commissionRecords) {
                $f->commission_due    = ($f->gross_revenue ?? 0) * ($f->commission_rate / 100);
                $f->commission_record = $commissionRecords->get($f->id);
                $f->commission_paid   = $f->commission_record?->commission_amount ?? 0;
                return $f;
            });

        $totalGross      = $franchises->sum('gross_revenue');
        $totalCommission = $franchises->sum('commission_due');

        return view('admin.commissions.index', compact(
            'franchises', 'month', 'totalGross', 'totalCommission'
        ));
    }

    public function calculate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'month'           => ['required', 'date_format:Y-m'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        [$year, $mo] = explode('-', $data['month']);

        $franchises = Franchise::where('status', 'active')->get();
        $created = 0;

        foreach ($franchises as $franchise) {
            $revenue = Payment::where('franchise_id', $franchise->id)
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $mo)
                ->sum('amount');

            if ($revenue > 0) {
                $rate = $data['commission_rate'] ?? $franchise->commission_rate;
                Commission::updateOrCreate(
                    ['franchise_id' => $franchise->id, 'month' => $data['month'] . '-01'],
                    [
                        'commission_amount' => $revenue * ($rate / 100),
                        'commission_rate'   => $rate,
                        'gross_revenue'     => $revenue,
                        'students_count'    => $franchise->students()->count(),
                        'fee_per_student'   => $franchise->fee_per_student,
                    ]
                );
                $created++;
            }
        }

        AuditLogger::log('commissions_calculated', 'Commission', null, null, ['month' => $data['month'], 'count' => $created]);

        return redirect()->route('admin.commissions.index', ['month' => $data['month']])
            ->with('success', "Commissions calculated for {$created} franchises.");
    }

    public function exportPdf(Request $request): Response
    {
        $month = $request->filled('month') ? $request->month : now()->format('Y-m');
        [$year, $mo] = explode('-', $month);
        $monthDate = $year . '-' . str_pad($mo, 2, '0', STR_PAD_LEFT) . '-01';

        $commissionRecords = Commission::where('month', $monthDate)->get()->keyBy('franchise_id');

        $franchises = Franchise::where('status', 'active')
            ->withSum(['payments as gross_revenue' => function ($q) use ($year, $mo) {
                $q->whereYear('payment_date', $year)->whereMonth('payment_date', $mo);
            }], 'amount')
            ->get()
            ->map(function ($f) use ($commissionRecords) {
                $f->commission_due    = ($f->gross_revenue ?? 0) * ($f->commission_rate / 100);
                $f->commission_record = $commissionRecords->get($f->id);
                return $f;
            });

        $totalGross      = $franchises->sum('gross_revenue');
        $totalCommission = $franchises->sum('commission_due');

        $pdf = Pdf::loadView('admin.pdf.commissions', compact('franchises', 'month', 'totalGross', 'totalCommission'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('commissions-' . $month . '.pdf');
    }

    public function markPaid(Commission $commission): RedirectResponse
    {
        $commission->update([
            'status'  => 'paid',
            'paid_at' => now(),
            'paid_by' => auth()->id(),
        ]);
        AuditLogger::log('commission_paid', 'Commission', $commission->id);

        return back()->with('success', 'Commission marked as paid.');
    }
}
