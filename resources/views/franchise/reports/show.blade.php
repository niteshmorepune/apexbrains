@extends('layouts.franchise')
@section('title', $student->full_name . ' — Report')
@section('page-title', $student->full_name . ' — Progress Report')

@section('page-actions')
    <a href="{{ route('franchise.reports.pdf', $student) }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">
        Export PDF
    </a>
    <a href="{{ route('franchise.reports.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">← Reports</a>
@endsection

@section('content')

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2 space-y-4">

        {{-- Score history chart --}}
        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-sm font-semibold text-fran mb-4">Exam Score Trend</h2>
            @if($attempts->count() >= 2)
                <div class="h-40">
                    <canvas id="scoreChart"></canvas>
                </div>
            @elseif($attempts->count() === 1)
                <p class="text-sm text-gray-400 text-center py-6">Only 1 exam taken — chart appears after 2+ exams.</p>
            @else
                <p class="text-sm text-gray-400 text-center py-6">No exams taken yet.</p>
            @endif
        </div>

        {{-- Radar / Performance breakdown chart --}}
        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-sm font-semibold text-fran mb-4">Performance Breakdown</h2>
            @if($attempts->count() > 0)
                <div class="h-52 flex items-center justify-center">
                    <canvas id="radarChart"></canvas>
                </div>
            @else
                <p class="text-sm text-gray-400 text-center py-6">No data available yet.</p>
            @endif
        </div>

        {{-- Attempt table --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-sm font-semibold text-fran">All Exam Attempts</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-fran">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-white">Exam</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Date</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-white">Score</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($attempts as $attempt)
                        <tr class="hover:bg-bg-light">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ $attempt->exam?->title ?? 'Exam #' . $attempt->exam_id }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $attempt->submitted_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right font-bold
                                {{ $attempt->percentage >= 75 ? 'text-stu' : ($attempt->percentage >= 50 ? 'text-logo-amber' : 'text-red-500') }}">
                                {{ number_format($attempt->percentage, 1) }}%
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($attempt->is_passed)
                                    <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Passed</span>
                                @else
                                    <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Failed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No exam attempts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-fran mb-3">Student Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Level</dt><dd class="font-medium">{{ $student->currentLevel ? 'Level ' . $student->currentLevel->number : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Enrolled</dt><dd>{{ $student->enrollment_date?->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total Exams</dt><dd class="font-bold text-fran">{{ $attempts->count() }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Avg Score</dt><dd class="font-bold {{ $attempts->avg('percentage') >= 75 ? 'text-stu' : 'text-logo-amber' }}">{{ $attempts->count() ? number_format($attempts->avg('percentage'), 1) . '%' : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Passed</dt><dd class="text-stu font-medium">{{ $attempts->where('is_passed', true)->count() }}</dd></div>
            </dl>
        </div>
        <a href="{{ route('franchise.certificates.index') }}"
           class="block text-center py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            Generate Certificate
        </a>
    </div>
</div>

@push('scripts')
@if($attempts->count() >= 2)
<script>
const chartData = @json($chartData);
new Chart(document.getElementById('scoreChart'), {
    type: 'line',
    data: {
        labels: chartData.map(d => d.label),
        datasets: [{
            data: chartData.map(d => d.score),
            borderColor: '#1A73E8',
            backgroundColor: 'rgba(26,115,232,0.08)',
            borderWidth: 2, fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#1A73E8'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, max: 100, ticks: { callback: v => v + '%', font: { size: 10 } } },
            x: { ticks: { font: { size: 10 } } }
        }
    }
});
</script>
@endif
@if($attempts->count() > 0)
<script>
const radarData = @json($radarData);
new Chart(document.getElementById('radarChart'), {
    type: 'radar',
    data: {
        labels: radarData.labels,
        datasets: [{
            data: radarData.values,
            borderColor: '#1A73E8',
            backgroundColor: 'rgba(26,115,232,0.15)',
            borderWidth: 2,
            pointBackgroundColor: '#1A73E8',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            r: {
                min: 0, max: 100,
                ticks: { stepSize: 25, font: { size: 9 }, callback: v => v + '%' },
                pointLabels: { font: { size: 10 } }
            }
        }
    }
});
</script>
@endif
@endpush

@endsection
