<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Revenue Report</title>
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
        .footer { margin-top: 20px; font-size: 9px; color: #AAA; text-align: right; }
        .brand { font-size: 10px; color: #888; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
        <div>
            <h1>Revenue Analytics Report</h1>
            <p class="sub">Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:14px; font-weight:bold; color:#1A2332;">Apex Brains Academy</div>
            <div class="brand">Generated {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <div class="kpi-row">
        <div class="kpi">
            <div class="kpi-label">Total Revenue (Period)</div>
            <div class="kpi-value">&#8377;{{ number_format($totalRevenue) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Active Franchises</div>
            <div class="kpi-value" style="color:#1A2332;">{{ $branchRevenue->count() }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Avg Revenue / Franchise</div>
            <div class="kpi-value" style="color:#2ECC71;">&#8377;{{ $branchRevenue->count() > 0 ? number_format($totalRevenue / $branchRevenue->count()) : 0 }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Franchise</th>
                <th>City</th>
                <th class="right">Revenue (Period)</th>
                <th class="right">Commission Rate</th>
                <th class="right">Commission Due</th>
                <th class="right">Revenue Share</th>
            </tr>
        </thead>
        <tbody>
            @foreach($branchRevenue as $i => $f)
                @php $share = $totalRevenue > 0 ? round(($f->revenue / $totalRevenue) * 100, 1) : 0; @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $f->name }}</td>
                    <td>{{ $f->city }}</td>
                    <td class="right">&#8377;{{ number_format($f->revenue ?? 0) }}</td>
                    <td class="right">{{ $f->commission_rate }}%</td>
                    <td class="right">&#8377;{{ number_format(($f->revenue ?? 0) * ($f->commission_rate / 100)) }}</td>
                    <td class="right">{{ $share }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="font-weight:bold; background:#F0F4FF;">TOTAL</td>
                <td class="right" style="font-weight:bold; background:#F0F4FF;">&#8377;{{ number_format($totalRevenue) }}</td>
                <td class="right" style="background:#F0F4FF;">—</td>
                <td class="right" style="font-weight:bold; background:#F0F4FF;">&#8377;{{ number_format($branchRevenue->sum(fn($f) => ($f->revenue ?? 0) * ($f->commission_rate / 100))) }}</td>
                <td class="right" style="background:#F0F4FF;">100%</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Apex Brains Academy Management System &bull; Confidential</div>
</body>
</html>
