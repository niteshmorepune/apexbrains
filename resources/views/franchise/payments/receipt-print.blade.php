@php
    use Illuminate\Support\Facades\Storage;

    // Brand logo as a base64 data URI (dompdf can't fetch URLs).
    $logoData = null;
    $logoPath = $appSettings['logo_path'] ?? null;
    if ($logoPath && Storage::disk('public')->exists($logoPath)) {
        $logoData = 'data:image/png;base64,' . base64_encode(Storage::disk('public')->get($logoPath));
    } elseif (file_exists(public_path('images/apex-logo.png'))) {
        $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/apex-logo.png')));
    }

    if (! function_exists('receiptAmountWords')) {
        function receiptAmountWords(int $num): string {
            $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
            $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
            if ($num === 0) return 'Zero';
            if ($num < 20) return $ones[$num];
            if ($num < 100) return $tens[intdiv($num,10)] . ($num%10 ? ' '.$ones[$num%10] : '');
            if ($num < 1000) return $ones[intdiv($num,100)] . ' Hundred' . ($num%100 ? ' '.receiptAmountWords($num%100) : '');
            if ($num < 100000) return receiptAmountWords(intdiv($num,1000)) . ' Thousand' . ($num%1000 ? ' '.receiptAmountWords($num%1000) : '');
            return receiptAmountWords(intdiv($num,100000)) . ' Lakh' . ($num%100000 ? ' '.receiptAmountWords($num%100000) : '');
        }
    }

    $mode = match($payment->payment_mode) {
        'upi' => 'UPI', 'card' => 'Card', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', default => 'Cash',
    };
    $feeTypeLabel = $payment->fee?->fee_type
        ? ucwords(str_replace('_', ' ', $payment->fee->fee_type))
        : '—';
    $franchise = auth()->user()->franchise ?? null;

    // dompdf does not render inline <svg>; embed the QR as an <img> data URI instead.
    $qrData = base64_encode((string) QrCode::size(140)->margin(0)
        ->generate(route('franchise.payments.receipt', $payment)));
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; }
        .head { width: 100%; border-bottom: 2px solid #E11D48; padding-bottom: 10px; margin-bottom: 14px; }
        .head td { vertical-align: middle; }
        .brand { font-size: 18px; font-weight: bold; color: #1A2332; }
        .iso { color: #9ca3af; font-size: 10px; }
        .rtitle { color: #6b7280; font-size: 11px; font-weight: bold; text-transform: uppercase; text-align: right; }
        .branch { color: #6b7280; font-size: 10px; margin-bottom: 14px; }
        table.kv { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.kv td { padding: 5px 0; font-size: 11px; }
        table.kv .lbl { color: #9ca3af; width: 28%; }
        table.kv .val { color: #111827; font-weight: bold; }
        .words { background: #f3f4f6; border-radius: 8px; padding: 8px 10px; font-size: 11px; margin-bottom: 16px; }
        .words .c { color: #6b7280; }
        .foot { width: 100%; border-top: 1px solid #e5e7eb; padding-top: 12px; }
        .foot td { vertical-align: top; font-size: 10px; color: #6b7280; }
        .sig { color: #9ca3af; margin-top: 18px; }
        .note { text-align: center; color: #d1d5db; font-size: 9px; margin-top: 14px; }
        .qr { width: 72px; height: 72px; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width:54px;">
                @if($logoData)
                    {{-- dompdf ignores object-fit; constrain height only to keep aspect ratio --}}
                    <img src="{{ $logoData }}" style="height:42px;" alt="logo">
                @endif
            </td>
            <td>
                <div class="brand">{{ $appSettings['app_name'] ?? 'Apex Brains' }}</div>
                <div class="iso">ISO 9001:2015</div>
            </td>
            <td class="rtitle">Payment Receipt</td>
        </tr>
    </table>

    @if($franchise)
        <div class="branch">
            {{ $franchise->name }}@if($franchise->city), {{ $franchise->city }}@endif
            @if($franchise->phone) &middot; {{ $franchise->phone }} @endif
        </div>
    @endif

    @php
        $academicYear = now()->month >= 6
            ? now()->year . '–' . (now()->year + 1)
            : (now()->year - 1) . '–' . now()->year;
    @endphp
    <table class="kv">
        <tr><td class="lbl">Receipt Number</td><td class="val">{{ $payment->receipt_number }}</td>
            <td class="lbl">Date</td><td class="val">{{ $payment->payment_date?->format('d M Y') }}</td></tr>
        <tr><td class="lbl">Student Name</td><td class="val">{{ $payment->student?->full_name }}</td>
            <td class="lbl">Student ID</td><td class="val">{{ $payment->student?->student_code }}</td></tr>
        <tr><td class="lbl">Level</td><td class="val">{{ $payment->student?->currentLevel?->title ?? '—' }}</td>
            <td class="lbl">Academic Year</td><td class="val">{{ $academicYear }}</td></tr>
        <tr><td class="lbl">Fee Type</td><td class="val">{{ $feeTypeLabel }}</td>
            <td class="lbl">Month</td><td class="val">{{ $payment->fee?->month?->format('M Y') ?? '—' }}</td></tr>
        <tr><td class="lbl">Payment Mode</td><td class="val">{{ $mode }}</td>
            <td class="lbl">Amount Paid</td><td class="val">₹{{ number_format($payment->amount) }}</td></tr>
        @if($payment->transaction_reference)
            <tr><td class="lbl">Transaction ID</td><td class="val" colspan="3">{{ $payment->transaction_reference }}</td></tr>
        @endif
    </table>

    <div class="words">
        <span class="c">Amount in Words:</span>
        <strong>{{ receiptAmountWords((int) $payment->amount) }} Rupees Only</strong>
    </div>

    <table class="foot">
        <tr>
            <td>
                Received by: {{ $payment->recordedBy?->name ?? auth()->user()->name }}
                <div class="sig">Signature: ___________________</div>
            </td>
            <td style="text-align:right; width:90px;">
                <img class="qr" src="data:image/svg+xml;base64,{{ $qrData }}" width="72" height="72" alt="QR code">
                <div style="font-size:9px; color:#9ca3af; text-align:right;">Scan to verify</div>
            </td>
        </tr>
    </table>

    <div class="note">This is a computer-generated receipt.</div>
</body>
</html>
