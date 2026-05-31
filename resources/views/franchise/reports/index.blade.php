@extends('layouts.franchise')
@section('title', 'Progress Reports')
@section('page-title', 'Student Progress Reports')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- Top student radar preview --}}
@if($topStudent)
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-2xl border border-border p-5">
        <h3 class="text-sm font-semibold text-fran mb-3">
            {{ $topStudent->full_name }} — L{{ $topStudent->currentLevel?->number ?? '?' }} Performance
        </h3>
        <canvas id="radarChart" height="180"></canvas>
    </div>
    <div class="col-span-2 bg-white rounded-2xl border border-border p-5">
        <h3 class="text-sm font-semibold text-fran mb-3">Exam Score History — {{ $topStudent->full_name }}</h3>
        <canvas id="historyChart" height="150"></canvas>
    </div>
</div>
@endif

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4">
    <form method="GET" action="{{ route('franchise.reports.index') }}" class="flex items-center gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student..."
               class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran flex-1 min-w-40">
        <select name="sort" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="best_score" @selected(request('sort', 'best_score') === 'best_score')>Best Score</option>
            <option value="name" @selected(request('sort') === 'name')>Sort by Name</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Filter</button>
        <a href="{{ route('franchise.reports.export', request()->only('search', 'level')) }}"
           class="px-4 py-2 border border-border text-gray-600 rounded-xl text-sm hover:bg-bg-light flex-shrink-0">
            ↓ Export
        </a>
    </form>
</div>

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Avg Score</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Speed</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Last Exam</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Exams</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Eligible</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($students as $s)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($s->first_name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-800">{{ $s->full_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->currentLevel)
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full">L{{ $s->currentLevel->number }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="font-semibold {{ $s->avg_score >= 75 ? 'text-stu' : ($s->avg_score >= 50 ? 'text-logo-amber' : 'text-red-500') }}">
                            {{ $s->exam_count ? number_format($s->avg_score, 1) . '%' : '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500 text-xs">
                        @if($s->avg_speed)
                            @php $sp = (int)$s->avg_speed; @endphp
                            {{ $sp >= 60 ? floor($sp/60).'m '.($sp%60).'s' : $sp.'s' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $s->last_exam_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $s->exam_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($s->eligible)
                            <span class="text-xs bg-stu-light text-stu-dark font-medium px-2 py-0.5 rounded-full">Yes</span>
                        @else
                            <span class="text-xs text-gray-400">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('franchise.reports.show', $s) }}" class="text-xs text-fran hover:underline">View Report</a>
                            <a href="{{ route('franchise.reports.pdf', $s) }}" class="text-xs text-gray-500 hover:underline">Download</a>
                            @if($s->eligible)
                                <a href="{{ route('franchise.promotions.index') }}" class="text-xs text-stu hover:underline font-medium">Promote</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-gray-400">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@endsection

@push('scripts')
@if($topStudent && count($topRadarData) > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels: ['Speed', 'Accuracy', 'Consistency', 'Improvement', 'Completion'],
            datasets: [{ data: @json($topRadarData), borderColor: '#1A73E8', backgroundColor: 'rgba(26,115,232,0.15)', pointBackgroundColor: '#1A73E8' }]
        },
        options: { plugins: { legend: { display: false } }, scales: { r: { min: 0, max: 100, ticks: { stepSize: 25 } } } }
    });
});
</script>
@endif
@endpush
