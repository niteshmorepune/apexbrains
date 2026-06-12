<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeeController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->filled('month') ? $request->month : now()->format('Y-m');
        [$year, $mo] = explode('-', $month);

        $tab = $request->get('tab', 'all');

        $query = Fee::with(['student', 'student.currentLevel', 'payments'])
            ->whereYear('month', $year)
            ->whereMonth('month', $mo);

        if ($request->filled('search')) {
            $query->whereHas('student', fn($q) => $q->where('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('last_name', 'like', '%' . $request->search . '%'));
        }

        if (in_array($request->get('student_type'), ['internal', 'external'])) {
            $query->where('student_type', $request->student_type);
        }

        match ($tab) {
            'paid'    => $query->where('status', 'paid'),
            'pending' => $query->where('status', 'pending'),
            'partial' => $query->where('status', 'partial'),
            'overdue' => $query->where('status', 'overdue'),
            default   => null,
        };

        $fees = $query->orderBy('due_date')->paginate(25)->withQueryString();

        // KPIs
        $allFees = Fee::whereYear('month', $year)->whereMonth('month', $mo);
        $stats = [
            'collected'      => (clone $allFees)->where('status', 'paid')->sum('paid_amount'),
            'outstanding'    => (clone $allFees)->whereIn('status', ['pending', 'partial', 'overdue'])->sum('amount'),
            'overdue'        => (clone $allFees)->where('status', 'overdue')->sum('amount'),
            'collection_rate'=> (clone $allFees)->count() > 0
                ? round(((clone $allFees)->where('status', 'paid')->count() / (clone $allFees)->count()) * 100, 1)
                : 0,
            'paid_count'     => (clone $allFees)->where('status', 'paid')->count(),
            'pending_count'  => (clone $allFees)->whereIn('status', ['pending', 'partial', 'overdue'])->count(),
            'overdue_count'  => (clone $allFees)->where('status', 'overdue')->count(),
            'prev_rate'      => (function() use ($year, $mo) {
                $prev = Fee::whereYear('month', $mo == 1 ? $year - 1 : $year)->whereMonth('month', $mo == 1 ? 12 : $mo - 1);
                $total = (clone $prev)->count();
                return $total > 0 ? round((clone $prev)->where('status', 'paid')->count() / $total * 100, 1) : null;
            })(),
        ];

        $students = Student::where('is_active', true)->orderBy('first_name')->get();

        // Unpaid fees this month for the Quick Record Payment panel (independent of the active tab)
        $unpaidFees = (clone $allFees)->with('student')
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->get();

        return view('franchise.fees.index', compact('fees', 'stats', 'month', 'tab', 'students', 'unpaidFees'));
    }

    public function show(Fee $fee): View
    {
        $fee->load('student', 'payments');
        return view('franchise.fees.show', compact('fee'));
    }

    public function reminders(): View
    {
        $allFees = Fee::with(['student.currentLevel', 'student.primaryParent'])
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date');

        $fees = $allFees->get()->map(function ($fee) {
            // Carbon 3 diffInDays() returns a float; compare whole days only so
            // the UI shows "3 days" not "3.1287… days".
            $fee->overdue_days = $fee->due_date
                ? (int) $fee->due_date->copy()->startOfDay()->diffInDays(now()->startOfDay(), false)
                : 0;
            $fee->priority = match(true) {
                $fee->overdue_days > 60  => 'critical',
                $fee->overdue_days > 30  => 'high',
                $fee->overdue_days > 0   => 'medium',
                default                  => 'low',
            };
            return $fee;
        })->sortByDesc('overdue_days');

        $stats = [
            'due_this_month'  => Fee::whereIn('status', ['pending', 'partial'])->whereMonth('due_date', now()->month)->sum('amount'),
            'due_count'       => Fee::whereIn('status', ['pending', 'partial'])->whereMonth('due_date', now()->month)->count(),
            'overdue_30'      => Fee::where('status', 'overdue')->where('due_date', '<=', now()->subDays(30))->sum('amount'),
            'overdue_30_count'=> Fee::where('status', 'overdue')->where('due_date', '<=', now()->subDays(30))->count(),
            'overdue_60'      => Fee::where('status', 'overdue')->where('due_date', '<=', now()->subDays(60))->sum('amount'),
            'overdue_60_count'=> Fee::where('status', 'overdue')->where('due_date', '<=', now()->subDays(60))->count(),
            'total_outstanding'=> Fee::whereIn('status', ['pending', 'partial', 'overdue'])->sum('amount'),
            'total_count'      => Fee::whereIn('status', ['pending', 'partial', 'overdue'])->count(),
        ];

        return view('franchise.fees.reminders', compact('fees', 'stats'));
    }

    public function reminder(Fee $fee): RedirectResponse
    {
        // Real WhatsApp/SMS in Phase 6 — log for now
        \App\Services\AuditLogger::log('fee_reminder_sent', 'Fee', $fee->id);

        return back()->with('success', 'Reminder noted for ' . $fee->student?->full_name . '.');
    }
}
