@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Global Dashboard — ' . now()->format('M Y'))

@section('page-actions')
    <a href="{{ route('admin.dashboard.export') }}"
       class="inline-flex items-center gap-2 bg-fran text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-fran-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Export CSV
    </a>
    <a href="{{ route('admin.revenue') }}"
       class="inline-flex items-center gap-2 border border-border text-gray-600 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-bg-light transition-colors">
        Revenue Report →
    </a>
@endsection

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Students</p>
                <p class="text-2xl font-bold text-fran">{{ number_format($totalStudents) }}</p>
                <p class="text-xs text-fran mt-1">Across all franchises</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-fran-light flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-fran" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Active Franchises</p>
                <p class="text-2xl font-bold text-admin">{{ $activeFranchises }}</p>
                @if($pendingFranchises > 0)
                    <p class="text-xs text-logo-amber mt-1">{{ $pendingFranchises }} pending approval</p>
                @else
                    <p class="text-xs text-gray-400 mt-1">All approved</p>
                @endif
            </div>
            <div class="w-10 h-10 rounded-xl bg-bg-mid flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-admin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Monthly Revenue</p>
                <p class="text-2xl font-bold text-stu">₹{{ number_format($monthlyRevenue) }}</p>
                <p class="text-xs mt-1 {{ $revenueGrowth >= 0 ? 'text-stu' : 'text-red-500' }}">
                    {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}% vs last month
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-stu-light flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-stu" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Global Avg Score</p>
                <p class="text-2xl font-bold text-logo-amber">{{ number_format($avgScore, 1) }}%</p>
                <p class="text-xs text-logo-amber mt-1">Across all levels</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#FEF3E2">
                <svg class="w-5 h-5 text-logo-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
    </div>

</div>

{{-- Charts row --}}
<div class="grid grid-cols-3 gap-4 mb-6">

    {{-- Monthly Revenue Trend --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-admin mb-4">Monthly Revenue Trend</h2>
        <div class="h-48">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Level Distribution --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-admin mb-3">Students by Level</h2>
        <div class="h-48">
            <canvas id="levelChart"></canvas>
        </div>
    </div>

</div>

{{-- Bottom row: Branch Performance + Franchise Table --}}
<div class="grid grid-cols-3 gap-4">

    {{-- Branch Performance bar chart --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-admin mb-4">Branch Performance</h2>
        <div class="h-48">
            <canvas id="branchChart"></canvas>
        </div>
    </div>

    {{-- Franchise Overview Table --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-admin">Franchise Overview</h2>
            <a href="{{ route('admin.franchises.index') }}"
               class="text-xs text-fran hover:underline font-medium">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-admin">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-white">Branch</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-white">City</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-white">Students</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-white">Revenue</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-white">Avg Score</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-white">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($franchises as $f)
                        <tr class="hover:bg-bg-light transition-colors">
                            <td class="px-4 py-3 font-medium text-admin">{{ $f->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $f->city }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($f->students_count) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                {{ $f->monthly_revenue > 0 ? '₹' . number_format($f->monthly_revenue) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                {{ $f->avg_score > 0 ? number_format($f->avg_score, 1) . '%' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <x-status-badge :status="$f->status" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.franchises.show', $f) }}"
                                       class="text-fran text-xs font-medium hover:underline">View</a>
                                    <a href="{{ route('admin.franchises.edit', $f) }}"
                                       class="text-gray-500 text-xs hover:underline">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">
                                No franchises yet.
                                <a href="{{ route('admin.franchises.create') }}" class="text-fran hover:underline">Add the first one →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const trendData  = @json($monthlyTrend);
const levelData  = @json($levelDistribution);
const franchises = @json($franchises->take(6)->map(fn($f) => ['name' => $f->name, 'students' => $f->students_count]));

const BLUE   = '#1A73E8';
const GREEN  = '#2ECC71';
const AMBER  = '#F5A623';
const NAVY   = '#1A2332';
const BORDER = '#D0D7E2';

// Monthly Revenue Trend
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: trendData.map(r => r.label),
        datasets: [{
            data: trendData.map(r => r.total),
            borderColor: BLUE,
            backgroundColor: 'rgba(26,115,232,0.08)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: BLUE,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#A0AEC0' } },
            y: { grid: { color: BORDER }, ticks: { font: { size: 11 }, color: '#A0AEC0',
                callback: v => '₹' + (v >= 100000 ? (v/100000).toFixed(1) + 'L' : v.toLocaleString('en-IN')) } }
        }
    }
});

// Level Distribution Donut
const levelColors = ['#87CEEB','#2ECC71','#00BCD4','#FFD54F','#F5A623','#FF69B4',
                     '#D42B2B','#9C27B0','#1A73E8','#00897B','#FF6F00','#AD1457','#283593','#212121'];
new Chart(document.getElementById('levelChart'), {
    type: 'doughnut',
    data: {
        labels: levelData.map(r => r.label),
        datasets: [{ data: levelData.map(r => r.total), backgroundColor: levelColors, borderWidth: 1 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { font: { size: 10 }, boxWidth: 12, padding: 6 } } }
    }
});

// Branch Performance Bar
new Chart(document.getElementById('branchChart'), {
    type: 'bar',
    data: {
        labels: franchises.map(f => f.name.split(' ')[0]),
        datasets: [{
            data: franchises.map(f => f.students),
            backgroundColor: BLUE,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#A0AEC0' } },
            y: { grid: { color: BORDER }, ticks: { font: { size: 11 }, color: '#A0AEC0' } }
        }
    }
});
</script>
@endpush
