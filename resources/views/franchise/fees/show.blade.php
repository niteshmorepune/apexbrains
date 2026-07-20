@extends('layouts.franchise')
@section('title', 'Fee — ' . ($fee->student?->full_name ?? ''))
@section('page-title', 'Fee Detail')

@section('page-actions')
    @if($fee->status !== 'paid')
        <button onclick="document.getElementById('paymentModal').classList.remove('hidden'); document.getElementById('paymentModal').classList.add('flex')"
                class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">
            + Record Payment
        </button>
    @endif
    <a href="{{ route('franchise.fees.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Fees
    </a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    {{-- Summary card --}}
    <div class="bg-white rounded-2xl border border-border p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <h2 class="text-base font-bold text-gray-800">{{ $fee->student?->full_name }}</h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $fee->student?->student_code }}
                    @if($fee->student?->currentLevel)
                        · {{ $fee->student->currentLevel->title }}
                    @endif
                </p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full font-semibold
                {{ match($fee->status) {
                    'paid'    => 'bg-green-50 text-green-700',
                    'overdue' => 'bg-red-50 text-red-600',
                    'partial' => 'bg-yellow-100 text-yellow-700',
                    default   => 'bg-gray-100 text-gray-500',
                } }}">
                {{ ucfirst($fee->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Month</span>
                    <span class="font-medium">{{ $fee->month?->format('M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Fee Type</span>
                    <span class="font-medium">{{ ucfirst($fee->fee_type ?? 'Monthly') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Due Date</span>
                    <span class="font-medium {{ $fee->status === 'overdue' ? 'text-red-500' : '' }}">
                        {{ $fee->due_date?->format('d M Y') }}
                    </span>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Amount</span>
                    <span class="font-bold text-gray-800">₹{{ number_format($fee->amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Amount Paid</span>
                    <span class="font-bold text-green-600">₹{{ number_format($fee->paid_amount ?? 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Balance Due</span>
                    <span class="font-bold {{ $fee->amount - ($fee->paid_amount ?? 0) > 0 ? 'text-red-500' : 'text-gray-400' }}">
                        ₹{{ number_format($fee->amount - ($fee->paid_amount ?? 0)) }}
                    </span>
                </div>
            </div>
        </div>

        @if($fee->status !== 'paid')
            <div class="mt-5 pt-4 border-t border-border">
                <a href="{{ route('franchise.fees.reminder', $fee) }}"
                   onclick="return confirm('Send reminder to parent?')"
                   class="text-sm text-logo-amber hover:underline">
                    Send payment reminder
                </a>
            </div>
        @endif
    </div>

    {{-- Payment history --}}
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h3 class="text-sm font-semibold text-fran">Payment History</h3>
        </div>
        @forelse($fee->payments as $payment)
            <div class="px-5 py-4 flex items-center gap-4 border-b border-border last:border-0">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">₹{{ number_format($payment->amount) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_mode)) }}
                        @if($payment->transaction_reference)
                            · <span class="font-mono">{{ $payment->transaction_reference }}</span>
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">{{ $payment->payment_date?->format('d M Y') }}</p>
                    <p class="text-xs text-gray-400">{{ $payment->receipt_number }}</p>
                </div>
                <a href="{{ route('franchise.payments.receipt', $payment) }}"
                   class="text-xs text-fran hover:underline flex-shrink-0">Receipt</a>
            </div>
        @empty
            <div class="px-5 py-8 text-center text-xs text-gray-400">No payments recorded yet.</div>
        @endforelse
    </div>

</div>

{{-- Payment modal (only when balance due) --}}
@if($fee->status !== 'paid')
<div id="paymentModal" class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl mx-4">
        <h3 class="text-sm font-bold text-fran mb-1">Record Payment</h3>
        <p class="text-xs text-gray-500 mb-4">
            {{ $fee->student?->full_name }} — Balance: ₹{{ number_format($fee->amount - ($fee->paid_amount ?? 0)) }}
        </p>
        <form method="POST" action="{{ route('franchise.payments.store') }}">
            @csrf
            <input type="hidden" name="fee_id" value="{{ $fee->id }}">
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Amount (₹)</label>
                    <input type="number" name="amount" step="0.01" required
                           value="{{ $fee->amount - ($fee->paid_amount ?? 0) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Mode</label>
                    <select name="payment_mode" required class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / GPay / PhonePe</option>
                        <option value="card">Card</option>
                        <option value="cheque">Cheque</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Transaction Ref</label>
                    <input type="text" name="transaction_reference" placeholder="Optional"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button type="button"
                        onclick="document.getElementById('paymentModal').classList.add('hidden'); document.getElementById('paymentModal').classList.remove('flex')"
                        class="flex-1 py-2.5 border border-border rounded-xl text-sm text-gray-600">Cancel</button>
                <button type="submit" class="flex-1 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold">
                    Record &amp; Generate Receipt
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
