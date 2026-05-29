@extends('layouts.student')
@section('title', 'Practice Results')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="p-4 space-y-4">

    {{-- Score card --}}
    <div class="bg-stu rounded-2xl p-6 text-white text-center">
        <p class="text-5xl font-black mb-1">{{ $session->questions_correct }}/{{ $session->total_questions }}</p>
        <p class="text-white/70 text-sm">Correct</p>
        @if($vsYesterday != 0)
            <p class="text-sm mt-2 font-medium {{ $vsYesterday > 0 ? 'text-white' : 'text-white/60' }}">
                {{ $vsYesterday > 0 ? '🔥 Great Session! +' . $vsYesterday . ' vs yesterday' : '↓ ' . abs($vsYesterday) . '% vs yesterday' }}
            </p>
        @else
            <p class="text-white/60 text-sm mt-2">Keep it up!</p>
        @endif
    </div>

    {{-- 3 stat chips --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-lg font-bold text-stu">{{ number_format($session->accuracy, 0) }}%</p>
            <p class="text-xs text-gray-400 mt-0.5">Accuracy</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            @if($avgSpeed)
                <p class="text-lg font-bold text-fran">{{ $avgSpeed }}s</p>
                <p class="text-xs text-gray-400 mt-0.5">Avg Speed</p>
            @else
                <p class="text-lg font-bold text-gray-400">—</p>
                <p class="text-xs text-gray-400 mt-0.5">Avg Speed</p>
            @endif
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            @php
                $mins = $session->created_at && $session->completed_at
                    ? (int) ceil($session->completed_at->diffInSeconds($session->created_at) / 60) : 0;
            @endphp
            <p class="text-lg font-bold text-logo-amber">{{ $mins }}m</p>
            <p class="text-xs text-gray-400 mt-0.5">Duration</p>
        </div>
    </div>

    {{-- Performance rating --}}
    @php
        $rating = match(true) {
            $session->accuracy >= 95 => ['label' => 'Excellent!', 'color' => 'text-stu'],
            $session->accuracy >= 80 => ['label' => 'Very good', 'color' => 'text-fran'],
            $session->accuracy >= 65 => ['label' => 'Good',      'color' => 'text-logo-amber'],
            default                  => ['label' => 'Keep practicing', 'color' => 'text-gray-500'],
        };
    @endphp
    <p class="text-center text-sm font-semibold {{ $rating['color'] }}">{{ $rating['label'] }}</p>

    {{-- Speed Improvement chart --}}
    <div class="bg-white rounded-2xl border border-border p-4">
        <p class="text-xs font-semibold text-gray-600 mb-3">Speed Improvement</p>
        <canvas id="speedChart" height="80"></canvas>
    </div>

    {{-- Correct / Wrong / Total --}}
    <div class="grid grid-cols-3 gap-3 text-center">
        <div class="bg-green-50 rounded-2xl p-3">
            <p class="text-xl font-black text-green-600">{{ $session->questions_correct }}</p>
            <p class="text-xs text-green-600 mt-0.5">Correct</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-3">
            <p class="text-xl font-black text-red-500">{{ $session->total_questions - $session->questions_correct }}</p>
            <p class="text-xs text-red-500 mt-0.5">Wrong</p>
        </div>
        <div class="bg-bg-mid rounded-2xl p-3">
            <p class="text-xl font-black text-gray-600">{{ $session->total_questions }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-3">
        <a href="{{ route('student.practice.index') }}"
           class="block w-full py-3 bg-stu text-white rounded-2xl text-sm font-semibold text-center hover:bg-stu-dark transition-colors">
            Practice Again ⚡
        </a>
        <a href="{{ route('student.home') }}"
           class="block w-full py-3 border border-border text-gray-600 rounded-2xl text-sm font-semibold text-center hover:bg-bg-light transition-colors">
            Back to Home
        </a>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = @json($chartLabels);
    if (document.getElementById('speedChart')) {
        new Chart(document.getElementById('speedChart'), {
            type: 'bar',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    data: data.map(d => d.value),
                    backgroundColor: '#2ECC71',
                    borderRadius: 6,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { stepSize: 25, callback: v => v + '%' } },
                    x: { grid: { display: false } }
                },
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }
});
</script>
@endpush
@endsection
