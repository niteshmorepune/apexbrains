@extends('layouts.admin')
@section('title', 'Revenue Analytics')
@section('page-title', 'Revenue Analytics')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- Date range filter --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4 flex items-center gap-3">
    <form method="GET" action="{{ route('admin.revenue') }}" class="flex items-center gap-3">
        <div class="flex items-center gap-2 border border-border rounded-xl px-4 py-2">
            <input type="date" name="from" value="{{ $from }}"
                   class="text-sm border-none outline-none bg-transparent">
            <span class="text-gray-400">→</span>
            <input type="date" name="to" value="{{ $to }}"
                   class="text-sm border-none outline-none bg-transparent">
        </div>
        <button type="submit"
                class="px-4 py-2 bg-fran text-white text-sm font-semibold rounded-xl hover:bg-fran-dark transition-colors">
            Apply
        </button>
    </form>
    <a href="{{ route('admin.revenue.export-pdf', ['from' => $from, 'to' => $to]) }}"
       class="ml-auto inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Export PDF
    </a>
    <a href="{{ route('admin.commissions.index') }}"
       class="text-sm text-fran hover:underline font-medium">
        Commission Calculator →
    </a>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Total Revenue (Period)</p>
        <p class="text-2xl font-bold text-fran">₹{{ number_format($totalRevenue) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($from)->format('d M') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">This Month</p>
        <p class="text-2xl font-bold text-stu">₹{{ number_format($monthRevenue) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ now()->format('M Y') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Per Franchise Avg</p>
        <p class="text-2xl font-bold text-admin">₹{{ number_format($perFranchiseAvg) }}</p>
        <p class="text-xs text-gray-400 mt-1">Monthly</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Growth Rate</p>
        @if($growthRate !== null)
            <p class="text-2xl font-bold {{ $growthRate >= 0 ? 'text-stu' : 'text-red-500' }}">
                {{ $growthRate >= 0 ? '+' : '' }}{{ $growthRate }}%
            </p>
            <p class="text-xs text-gray-400 mt-1">vs same month last year</p>
        @else
            <p class="text-2xl font-bold text-gray-400">—</p>
            <p class="text-xs text-gray-400 mt-1">No prior year data</p>
        @endif
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="col-span-2 bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-admin mb-4">Monthly Revenue Trend — {{ now()->format('Y') }}</h2>
        @if($monthlyTrend->isNotEmpty())
            <div class="h-52">
                <canvas id="trendChart"></canvas>
            </div>
        @else
            <div class="h-52 flex flex-col items-center justify-center text-gray-400">
                <svg class="w-8 h-8 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-sm font-medium">No revenue recorded yet</p>
                <p class="text-xs mt-1">Chart will appear once payments are collected</p>
            </div>
        @endif
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-admin mb-4">Branch Revenue Share</h2>
        @if($branchRevenue->sum('revenue') > 0)
            <div class="h-52">
                <canvas id="shareChart"></canvas>
            </div>
        @else
            <div class="h-52 flex flex-col items-center justify-center text-gray-400">
                <svg class="w-8 h-8 mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
                <p class="text-sm font-medium">No branch revenue yet</p>
                <p class="text-xs mt-1">Chart will appear once branches collect payments</p>
            </div>
        @endif
    </div>
</div>

{{-- Commission breakdown table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border">
        <h2 class="text-sm font-semibold text-admin">Commission Breakdown by Franchise</h2>
    </div>
    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Franchise</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">City</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Students</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Fee/Student</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Gross Revenue</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Commission %</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Net Commission</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($branchRevenue as $f)
                @php
                    $gross = $f->revenue ?? 0;
                    $commissionDue = $gross * ($f->commission_rate / 100);
                    $cs = $f->commission_status ?? null;
                @endphp
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3 font-medium text-admin">{{ $f->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $f->city }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ number_format($f->students_count) }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">₹{{ number_format($f->fee_per_student) }}</td>
                    <td class="px-4 py-3 text-right">₹{{ number_format($gross) }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $f->commission_rate }}%</td>
                    <td class="px-4 py-3 text-right font-medium text-fran">₹{{ number_format($commissionDue) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($cs === 'paid')
                            <span class="text-xs bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full">Paid</span>
                        @elseif($cs === 'partial')
                            <span class="text-xs bg-blue-100 text-blue-700 font-medium px-2 py-0.5 rounded-full">Partial</span>
                        @elseif($cs === 'pending')
                            <span class="text-xs bg-yellow-100 text-yellow-700 font-medium px-2 py-0.5 rounded-full">Pending</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-500 font-medium px-2 py-0.5 rounded-full">Not calculated</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-8 text-center text-gray-400">No revenue data for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@endsection

@push('scripts')
<script>
if (document.getElementById('trendChart')) {
    const trend = @json($monthlyTrend);
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trend.map(r => r.label),
            datasets: [{
                data: trend.map(r => r.total),
                borderColor: '#1A73E8', backgroundColor: 'rgba(26,115,232,0.08)',
                borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#1A73E8'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#A0AEC0' } },
                y: { grid: { color: '#D0D7E2' }, ticks: { font: { size: 10 }, color: '#A0AEC0',
                    callback: v => '₹' + (v >= 100000 ? (v/100000).toFixed(1)+'L' : v) } }
            }
        }
    });
}

if (document.getElementById('shareChart')) {
    const branches = @json($branchRevenue->map(fn($f) => ['name' => explode(' ', $f->name)[0], 'rev' => (float)($f->revenue ?? 0)]));
    const colors = ['#1A73E8','#2ECC71','#F5A623','#D42B2B','#9C27B0','#00BCD4','#FF6F00','#283593'];
    new Chart(document.getElementById('shareChart'), {
        type: 'doughnut',
        data: {
            labels: branches.map(b => b.name),
            datasets: [{ data: branches.map(b => b.rev), backgroundColor: colors, borderWidth: 1 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12, padding: 8 } } }
        }
    });
}
</script>
@endpush
