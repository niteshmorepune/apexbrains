@extends('layouts.external')
@section('title', 'Result — ' . $paper->title)

@section('content')
@php
    $total      = $paper->total_questions ?: max(1, ($questions->count() ?: 1));
    $score      = (int) $attempt->score;
    $pct        = (int) round($attempt->percentage);
    $wrong      = max(0, $total - $score);
    $durationSec = $attempt->started_at && $attempt->submitted_at ? $attempt->submitted_at->diffInSeconds($attempt->started_at) : 0;
    $timeMins   = $durationSec > 0 ? floor($durationSec/60).':'.str_pad($durationSec%60, 2, '0', STR_PAD_LEFT) : '—';
    $avgSpeed   = $total > 0 && $durationSec > 0 ? round($durationSec / $total, 1) : null;
    $passed     = $pct >= ($paper->pass_percentage ?? 75);
    $circ       = 2 * 3.14159 * 52;
@endphp

<div class="px-4 pt-6 pb-4 space-y-4">

    {{-- Score ring --}}
    <div class="flex flex-col items-center">
        <div class="relative w-36 h-36">
            <svg class="w-36 h-36 -rotate-90" viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="52" fill="none" stroke="#EDF0F5" stroke-width="10"/>
                <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $passed ? '#2ECC71' : '#1A73E8' }}" stroke-width="10" stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ - ($circ * $pct / 100) }}"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <p class="text-2xl font-black text-gray-900">{{ $score }}/{{ $total }}</p>
                <p class="text-xs text-gray-400">Correct</p>
            </div>
        </div>
        <p class="text-base font-bold text-gray-800 mt-3">{{ $paper->title }}</p>
        <span class="inline-block font-bold text-xs px-4 py-1 rounded-full mt-1.5 {{ $passed ? 'bg-stu text-white' : 'bg-fran text-white' }}">{{ $passed ? 'PASSED' : 'COMPLETED' }}</span>
    </div>

    {{-- Stats 2x2 --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-stu text-xl">✅</span><div><p class="text-lg font-black text-stu leading-none">{{ $score }}/{{ $total }}</p><p class="text-xs text-gray-400 mt-1">Correct</p></div></div>
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-red-500 text-xl">❌</span><div><p class="text-lg font-black text-red-500 leading-none">{{ $wrong }}/{{ $total }}</p><p class="text-xs text-gray-400 mt-1">Wrong</p></div></div>
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-fran text-xl">⚡</span><div><p class="text-lg font-black text-fran leading-none">{{ $avgSpeed ? $avgSpeed.'s' : '—' }}</p><p class="text-xs text-gray-400 mt-1">Avg Speed</p></div></div>
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-logo-amber text-xl">⏱️</span><div><p class="text-lg font-black text-gray-700 leading-none">{{ $timeMins }}</p><p class="text-xs text-gray-400 mt-1">Time Taken</p></div></div>
    </div>

    {{-- Actions --}}
    <div class="space-y-3 pt-1">
        <form method="POST" action="{{ route('external.practice.start', $paper) }}">
            @csrf
            <button type="submit" class="w-full py-3.5 bg-fran text-white rounded-2xl text-sm font-bold">Try Again</button>
        </form>
        <a href="{{ route('external.practice.index') }}" class="block w-full py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold text-center">All Papers</a>
        <a href="{{ route('external.home') }}" class="block w-full text-center text-xs text-gray-400 py-1">Back to Home</a>
    </div>

</div>
@endsection
