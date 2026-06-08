@extends('layouts.student')
@section('title', 'Practice Results')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
@php
    $correct = $session->questions_correct ?? 0;
    $total   = $session->total_questions ?: 1;
    $pct     = round($correct / $total * 100);
    $mins    = $session->created_at && $session->completed_at
        ? (int) ceil($session->completed_at->diffInSeconds($session->created_at) / 60) : ($session->duration_minutes ?? 0);
    $rating = match(true) {
        $session->accuracy >= 95 => ['label' => 'Excellent!',  'emoji' => '🏆', 'color' => 'text-stu'],
        $session->accuracy >= 80 => ['label' => 'Very good',   'emoji' => '🏆', 'color' => 'text-logo-amber'],
        $session->accuracy >= 65 => ['label' => 'Good',        'emoji' => '👍', 'color' => 'text-fran'],
        default                  => ['label' => 'Keep going',  'emoji' => '💪', 'color' => 'text-gray-500'],
    };
    $circ = 2 * 3.14159 * 52;
@endphp

<div class="px-4 pt-6 pb-4 space-y-4">

    {{-- Score ring --}}
    <div class="flex flex-col items-center">
        <div class="relative w-36 h-36">
            <svg class="w-36 h-36 -rotate-90" viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="52" fill="none" stroke="#EDF0F5" stroke-width="10"/>
                <circle cx="60" cy="60" r="52" fill="none" stroke="#2ECC71" stroke-width="10" stroke-linecap="round"
                        stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ - ($circ * $pct / 100) }}"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <p class="text-2xl font-black text-gray-900">{{ $correct }}/{{ $session->total_questions }}</p>
                <p class="text-xs text-gray-400">Correct</p>
            </div>
        </div>
        <p class="text-sm font-semibold text-gray-700 mt-3">
            @if($vsYesterday > 0)
                Great Session! <span class="text-stu">+{{ $vsYesterday }} vs yesterday</span>
            @elseif($vsYesterday < 0)
                Session done · <span class="text-red-500">{{ $vsYesterday }} vs yesterday</span>
            @else
                Session complete!
            @endif
        </p>
    </div>

    {{-- 2x2 stat grid --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4">
            <span class="text-stu text-lg">🎯</span>
            <p class="text-xl font-black text-stu mt-1">{{ number_format($session->accuracy ?? 0, 0) }}%</p>
            <p class="text-xs text-gray-400">Accuracy</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <span class="text-fran text-lg">⚡</span>
            <p class="text-xl font-black text-fran mt-1">{{ $avgSpeed ? $avgSpeed.'s' : '—' }}</p>
            <p class="text-xs text-gray-400">Avg Speed</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <span class="text-logo-amber text-lg">⏱️</span>
            <p class="text-xl font-black text-logo-amber mt-1">{{ $mins }} min</p>
            <p class="text-xs text-gray-400">Duration</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <span class="text-lg">{{ $rating['emoji'] }}</span>
            <p class="text-xl font-black {{ $rating['color'] }} mt-1">{{ $rating['label'] }}</p>
            <p class="text-xs text-gray-400">Rating</p>
        </div>
    </div>

    {{-- Speed Improvement chart --}}
    <div class="bg-white rounded-2xl border border-border p-4">
        <p class="text-sm font-bold text-gray-800 mb-3">Speed Improvement</p>
        {{-- Fixed-height wrapper: required so Chart.js (maintainAspectRatio:false) doesn't grow the canvas indefinitely --}}
        <div class="relative h-36">
            <canvas id="speedChart"></canvas>
        </div>
    </div>

    {{-- Correct / Wrong --}}
    <div class="grid grid-cols-2 gap-3 text-center">
        <div class="bg-stu-light rounded-2xl p-3">
            <p class="text-xl font-black text-stu">{{ $correct }}</p>
            <p class="text-xs text-stu mt-0.5">Correct</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-3">
            <p class="text-xl font-black text-red-500">{{ $session->total_questions - $correct }}</p>
            <p class="text-xs text-red-500 mt-0.5">Wrong</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-3 pt-1">
        <a href="{{ route('student.practice.index') }}" class="block w-full py-3.5 bg-fran text-white rounded-2xl text-sm font-bold text-center">Practice Again</a>
        <a href="{{ route('student.home') }}" class="block w-full py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold text-center">Back to Home</a>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = @json($chartLabels);
    const el = document.getElementById('speedChart');
    if (el && window.Chart) {
        new Chart(el, {
            type: 'line',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    data: data.map(d => d.value),
                    borderColor: '#2ECC71',
                    backgroundColor: 'rgba(46,204,113,0.12)',
                    fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#2ECC71', borderWidth: 2,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100, ticks: { stepSize: 25, callback: v => v + '%' } }, x: { grid: { display: false } } },
                responsive: true, maintainAspectRatio: false,
            }
        });
    }
});
</script>
@endpush
@endsection
