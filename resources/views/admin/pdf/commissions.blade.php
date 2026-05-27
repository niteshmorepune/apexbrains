<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Commission Report</title>
    <style>
        body  { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1A2332; margin: 0; padding: 20px; }
        h1    { font-size: 18px; color: #1A2332; margin-bottom: 4px; }
        .sub  { color: #666; font-size: 11px; margin-bottom: 20px; }
        .kpi-row { display: flex; gap: 16px; margin-bottom: 20px; }
        .kpi  { flex: 1; border: 1px solid #D0D7E2; border-radius: 8px; padding: 10px 14px; }
        .kpi-label { font-size: 9px; color: #888; margin-bottom: 4px; }
        .kpi-value { font-size: 18px; font-weight: bold; color: #1A73E8; }
        table { width: 100%; border-collapse: collapse; }
        th    { background: #1A2332; color: #fff; text-align: left; padding: 7px 10px; font-size: 10px; }
        td    { padding: 7px 10px; border-bottom: 1px solid #E8EDF2; font-size: 10px; }
        tr:nth-child(even) td { background: #F7F9FC; }
        .right { text-align: right; }
        .badge-paid    { background:#D4EDDA; color:#155724; padding:2px 6px; border-radius:4px; }
        .badge-pending { background:#FFF3CD; color:#856404; padding:2px 6px; border-radius:4px; }
        .footer { margin-top: 20px; font-size: 9px; color: #AAA; text-align: right; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
        <div>
            <h1>Commission Report</h1>
            <p class="sub">Month: {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:14px; font-weight:bold; color:#1A2332;">Apex Brains Academy</div>
            <div style="font-size:10px; color:#888;">Generated {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <div class="kpi-row">
        <div class="kpi">
            <div class="kpi-label">Total Gross Revenue</div>
            <div class="kpi-value">&#8377;{{ number_format($totalGross) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Total Commission Due</div>
            <div class="kpi-value" style="color:#F5A623;">&#8377;{{ number_format($totalCommission) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Active Franchises</div>
            <div class="kpi-value" style="color:#1A2332;">{{ $franchises->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Franchise</th>
                <th class="right">Gross Revenue</th>
                <th class="right">Rate</th>
                <th class="right">Commission Due</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($franchises as $i => $f)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $f->name }}</td>
                    <td class="right">&#8377;{{ number_format($f->gross_revenue ?? 0) }}</td>
                    <td class="right">{{ $f->commission_rate }}%</td>
                    <td class="right" style="font-weight:bold;">&#8377;{{ number_format($f->commission_due) }}</td>
                    <td>
                        @if($f->commission_record?->status === 'paid')
                            <span class="badge-paid">Paid</span>
                        @elseif($f->commission_due > 0)
                            <span class="badge-pending">Pending</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="font-weight:bold; background:#F0F4FF;">TOTAL</td>
                <td class="right" style="font-weight:bold; background:#F0F4FF;">&#8377;{{ number_format($totalGross) }}</td>
                <td style="background:#F0F4FF;">—</td>
                <td class="right" style="font-weight:bold; background:#F0F4FF;">&#8377;{{ number_format($totalCommission) }}</td>
                <td style="background:#F0F4FF;"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Apex Brains Academy Management System &bull; Confidential</div>
</body>
</html>
