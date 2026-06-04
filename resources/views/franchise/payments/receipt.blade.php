@extends('layouts.franchise')
@section('title', 'Receipt ' . $payment->receipt_number)
@section('page-title', 'Payment Receipt — ' . $payment->receipt_number)

@section('page-actions')
    <a href="{{ route('franchise.payments.receipt.pdf', $payment) }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50 transition-colors">
        Download PDF
    </a>
    @if($payment->student?->primaryParent?->whatsapp ?? $payment->student?->parent_whatsapp)
        @php $wa = $payment->student->primaryParent?->whatsapp ?? $payment->student->parent_whatsapp; @endphp
        <a href="https://wa.me/91{{ preg_replace('/\D/', '', $wa) }}?text={{ urlencode('Receipt #' . $payment->receipt_number . ' for ₹' . number_format($payment->amount) . ' — ' . $payment->student?->full_name . '. View: ' . route('franchise.payments.receipt', $payment)) }}"
           target="_blank"
           class="px-4 py-2 bg-stu text-white rounded-xl text-sm font-semibold hover:bg-stu-dark transition-colors">
            Share WhatsApp
        </a>
    @endif
    <a href="{{ route('franchise.fees.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Fees
    </a>
@endsection

@section('content')

{{-- Print only the receipt card — hide the portal chrome (sidebar, header, buttons). --}}
<style>
    @media print {
        body * { visibility: hidden !important; }
        #receipt, #receipt * { visibility: visible !important; }
        #receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            box-shadow: none !important;
        }
        @page { margin: 14mm; }
    }
</style>

<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-border p-8" id="receipt">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6 pb-5 border-b border-border">
            <div class="flex items-center gap-3">
                @php
                    $receiptLogo = !empty($appSettings['logo_path'] ?? null)
                        ? \Illuminate\Support\Facades\Storage::url($appSettings['logo_path'])
                        : asset('images/apex-logo.png');
                @endphp
                <img src="{{ $receiptLogo }}" alt="{{ $appSettings['app_name'] ?? 'Apex Brains' }}"
                     class="w-12 h-12 rounded-xl object-contain">
                <div>
                    <p class="font-black text-admin text-lg leading-tight">{{ $appSettings['app_name'] ?? 'Apex Brains' }}</p>
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
        @php
        $academicYear = now()->month >= 6
            ? now()->year . '–' . (now()->year + 1)
            : (now()->year - 1) . '–' . now()->year;
        @endphp
        <div class="grid grid-cols-2 gap-x-8 gap-y-3 mb-6">
            @foreach([
                'Receipt Number' => $payment->receipt_number,
                'Date'           => $payment->payment_date?->format('d M Y'),
                'Student Name'   => $payment->student?->full_name,
                'Student ID'     => $payment->student?->student_code,
                'Level'          => $payment->student?->currentLevel ? 'Level ' . $payment->student->currentLevel->number : '—',
                'Academic Year'  => $academicYear,
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
        @php
        function numberToWords(int $num): string {
            $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                     'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                     'Seventeen','Eighteen','Nineteen'];
            $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
            if ($num === 0) return 'Zero';
            if ($num < 20) return $ones[$num];
            if ($num < 100) return $tens[intdiv($num,10)] . ($num%10 ? ' '.$ones[$num%10] : '');
            if ($num < 1000) return $ones[intdiv($num,100)] . ' Hundred' . ($num%100 ? ' '.numberToWords($num%100) : '');
            if ($num < 100000) return numberToWords(intdiv($num,1000)) . ' Thousand' . ($num%1000 ? ' '.numberToWords($num%1000) : '');
            return numberToWords(intdiv($num,100000)) . ' Lakh' . ($num%100000 ? ' '.numberToWords($num%100000) : '');
        }
        @endphp
        <div class="bg-bg-light rounded-xl p-3 mb-6">
            <p class="text-xs text-gray-500">Amount in Words:</p>
            <p class="text-sm font-medium text-gray-800">{{ numberToWords((int)$payment->amount) }} Rupees Only</p>
        </div>

        {{-- Footer --}}
        <div class="flex items-start justify-between border-t border-border pt-5">
            <div>
                <p class="text-xs text-gray-500">Received by: {{ $payment->recordedBy?->name ?? auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 mt-1">Signature: ___________________</p>
            </div>
            <div class="text-right">
                <div class="w-16 h-16">
                    {!! QrCode::size(64)->generate(route('franchise.payments.receipt', $payment)) !!}
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
            ← Back to Fees
        </a>
        <a href="{{ route('franchise.payments.receipt.pdf', $payment) }}"
           class="flex-1 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold text-center">
            Download PDF
        </a>
        <button onclick="window.print()"
                class="flex-1 py-2.5 border border-fran text-fran rounded-xl text-sm font-semibold">
            Print Receipt
        </button>
    </div>
</div>

@endsection
