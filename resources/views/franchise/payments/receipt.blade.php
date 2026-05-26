@extends('layouts.franchise')
@section('title', 'Receipt ' . $payment->receipt_number)
@section('page-title', 'Payment Receipt')

@section('page-actions')
    <button onclick="window.print()"
            class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">
        Print
    </button>
    <a href="{{ route('franchise.fees.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Fees
    </a>
@endsection

@section('content')

<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-border p-8" id="receipt">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6 pb-5 border-b border-border">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-logo-red flex items-center justify-center text-white font-black text-lg">AB</div>
                <div>
                    <p class="font-black text-admin text-lg leading-tight">Apex Brains</p>
                    <p class="text-xs text-gray-400">ISO 9001:2015</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Payment Receipt</p>
                <p class="text-xs text-gray-400">Official receipt for fee payment</p>
            </div>
        </div>

        {{-- Branch info --}}
        <p class="text-xs text-gray-500 mb-6">
            {{ auth()->user()->franchise->name ?? '' }},
            {{ auth()->user()->franchise->city ?? '' }}
            @if(auth()->user()->franchise->phone)
                &nbsp;·&nbsp; {{ auth()->user()->franchise->phone }}
            @endif
        </p>

        {{-- Receipt details --}}
        <div class="grid grid-cols-2 gap-x-8 gap-y-3 mb-6">
            @foreach([
                'Receipt Number' => $payment->receipt_number,
                'Date'           => $payment->payment_date?->format('d M Y'),
                'Student Name'   => $payment->student?->full_name,
                'Student ID'     => $payment->student?->student_code,
                'Level'          => $payment->student?->currentLevel ? 'Level ' . $payment->student->currentLevel->number : '—',
                'Month'          => $payment->fee?->month?->format('M Y'),
                'Amount Paid'    => '₹' . number_format($payment->amount),
                'Payment Mode'   => match($payment->payment_mode) {
                    'upi'           => 'UPI',
                    'card'          => 'Card',
                    'cheque'        => 'Cheque',
                    'bank_transfer' => 'Bank Transfer',
                    default         => 'Cash',
                },
            ] as $label => $value)
                <div class="flex flex-col">
                    <span class="text-xs text-gray-400">{{ $label }}:</span>
                    <span class="text-sm font-semibold text-gray-800">{{ $value }}</span>
                </div>
            @endforeach
            @if($payment->transaction_reference)
                <div class="col-span-2 flex flex-col">
                    <span class="text-xs text-gray-400">Transaction ID:</span>
                    <span class="text-sm font-mono text-gray-800">{{ $payment->transaction_reference }}</span>
                </div>
            @endif
        </div>

        {{-- Amount in words --}}
        <div class="bg-bg-light rounded-xl p-3 mb-6">
            <p class="text-xs text-gray-500">Amount in Words:</p>
            <p class="text-sm font-medium text-gray-800">₹{{ number_format($payment->amount) }} — Paid</p>
        </div>

        {{-- Footer --}}
        <div class="flex items-start justify-between border-t border-border pt-5">
            <div>
                <p class="text-xs text-gray-500">Received by: {{ $payment->recordedBy?->name ?? auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 mt-1">Signature: ___________________</p>
            </div>
            <div class="text-right">
                <div class="w-16 h-16 bg-bg-mid rounded-lg flex items-center justify-center">
                    <span class="text-xs text-gray-400">QR</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">Scan to verify</p>
            </div>
        </div>

        <p class="text-xs text-gray-300 text-center mt-4">This is a computer-generated receipt.</p>
    </div>

    {{-- Action buttons (hidden when printing) --}}
    <div class="flex gap-3 mt-4 print:hidden">
        <a href="{{ route('franchise.fees.index') }}"
           class="flex-1 py-2.5 border border-border rounded-xl text-sm text-center text-gray-600 hover:bg-bg-light">
            Back to Fees
        </a>
        <button onclick="window.print()"
                class="flex-1 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold">
            Download / Print
        </button>
    </div>
</div>

@endsection
