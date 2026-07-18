<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fee_id'                => ['required', 'exists:fees,id'],
            'amount'                => ['required', 'numeric', 'min:1'],
            'payment_mode'          => ['required', 'in:cash,upi,card,cheque,bank_transfer'],
            'payment_date'          => ['required', 'date'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ]);

        $fee = Fee::findOrFail($data['fee_id']);

        $franchiseId   = Auth::user()->franchise_id;
        $franchise     = Auth::user()->franchise;
        $receiptSeq    = Payment::count() + 1;
        $receiptNumber = strtoupper(substr($franchise->franchise_code ?? 'REC', 0, 2))
                       . '-' . now()->year . '-' . str_pad($receiptSeq, 4, '0', STR_PAD_LEFT);

        // Capture existing paid total BEFORE inserting the new payment to avoid double-counting
        $existingPaid = $fee->payments()->sum('amount');

        $payment = Payment::create([
            'franchise_id'          => $franchiseId,
            'student_id'            => $fee->student_id,
            'fee_id'                => $fee->id,
            'receipt_number'        => $receiptNumber,
            'amount'                => $data['amount'],
            'payment_mode'          => $data['payment_mode'],
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'payment_date'          => $data['payment_date'],
            'notes'                 => $data['notes'] ?? null,
            'recorded_by'           => Auth::id(),
        ]);

        $totalPaid = $existingPaid + $data['amount'];
        $isFullyPaid = $totalPaid >= $fee->amount;
        $fee->update([
            'paid_amount' => $totalPaid,
            'status'      => $isFullyPaid ? 'paid' : 'partial',
        ]);

        // Once a monthly tuition fee is fully paid, roll the next month's pending
        // fee forward automatically so collection continues without manual setup.
        if ($isFullyPaid) {
            app(\App\Services\MonthlyFeeService::class)->createNextMonthFee($fee->fresh());

            // A competition registration fee, once fully paid, flips the
            // registration's own status so its "Payment Pending" badge clears.
            if ($fee->fee_type === 'competition_registration' && $fee->competition_registration_id) {
                \App\Models\CompetitionRegistration::where('id', $fee->competition_registration_id)
                    ->update(['payment_status' => 'paid', 'payment_id' => $payment->id]);
            }
        }

        AuditLogger::log('payment_recorded', 'Payment', $payment->id);

        return redirect()->route('franchise.payments.receipt', $payment)
            ->with('success', "Payment recorded. Receipt #{$receiptNumber} generated.");
    }

    public function recordPage(Request $request): View
    {
        $students = \App\Models\Student::with([
                'currentLevel',
                'fees' => fn($q) => $q->where('status', '!=', 'paid')
                    ->with('competitionRegistration.competition')
                    ->orderBy('due_date'),
            ])
            ->where('is_active', true)->orderBy('first_name')->get();

        $selectedStudent = $request->filled('student_id')
            ? $students->firstWhere('id', $request->student_id)
            : null;

        return view('franchise.payments.record', compact('students', 'selectedStudent'));
    }

    public function receipt(Payment $payment): View
    {
        $payment->load('student.currentLevel', 'fee', 'recordedBy');
        return view('franchise.payments.receipt', compact('payment'));
    }

    public function receiptPdf(Payment $payment): \Illuminate\Http\Response
    {
        $payment->load('student.currentLevel', 'fee', 'recordedBy');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('franchise.payments.receipt-print', compact('payment'))
            ->setPaper('a5', 'portrait');

        return $pdf->download('receipt-' . $payment->receipt_number . '.pdf');
    }
}
