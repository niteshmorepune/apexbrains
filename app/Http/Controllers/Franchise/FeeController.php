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

        $query = Fee::with(['student', 'student.currentLevel'])
            ->whereYear('month', $year)
            ->whereMonth('month', $mo);

        if ($request->filled('search')) {
            $query->whereHas('student', fn($q) => $q->where('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('last_name', 'like', '%' . $request->search . '%'));
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
        ];

        $students = Student::where('is_active', true)->orderBy('first_name')->get();

        return view('franchise.fees.index', compact('fees', 'stats', 'month', 'tab', 'students'));
    }

    public function show(Fee $fee): View
    {
        $fee->load('student', 'payments');
        return view('franchise.fees.show', compact('fee'));
    }

    public function reminder(Fee $fee): RedirectResponse
    {
        // Real WhatsApp/SMS in Phase 6 — log for now
        \App\Services\AuditLogger::log('fee_reminder_sent', 'Fee', $fee->id);

        return back()->with('success', 'Reminder noted for ' . $fee->student?->full_name . '.');
    }
}
