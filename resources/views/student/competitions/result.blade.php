@extends('layouts.student')
@section('title', 'Competition Result')

@section('content')
<div class="p-4 space-y-4">

    @if($attempt)
        @php
            $passed = $attempt->percentage >= 75;
            $durationSec = $attempt->started_at && $attempt->submitted_at
                ? $attempt->submitted_at->diffInSeconds($attempt->started_at) : 0;
            $timeMins = $durationSec > 0
                ? floor($durationSec/60).':'.str_pad($durationSec%60, 2, '0', STR_PAD_LEFT) : '—';
            $totalQ = $attempt->paper?->total_questions ?? 0;
        @endphp

        {{-- Result banner --}}
        <div class="rounded-2xl p-6 text-white text-center {{ $passed ? 'bg-stu' : 'bg-red-500' }}">
            <p class="text-white/80 text-sm">{{ $competition->title }}</p>
            <p class="text-white/60 text-xs mt-0.5">Completed via {{ $attempt->paper?->title }}</p>
            <p class="text-5xl font-black my-2">{{ number_format($attempt->percentage, 0) }}%</p>
            <span class="inline-block font-bold text-sm px-4 py-1 rounded-full
                {{ $passed ? 'bg-white/20 text-white' : 'bg-white text-red-500' }}">
                {{ $passed ? 'PASSED' : 'FAILED' }}
            </span>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-green-50 rounded-2xl p-4 text-center">
                <p class="text-2xl font-black text-green-600">{{ (int) $attempt->score }}</p>
                <p class="text-xs text-green-600 mt-0.5">Correct</p>
            </div>
            <div class="bg-red-50 rounded-2xl p-4 text-center">
                <p class="text-2xl font-black text-red-500">{{ max(0, $totalQ - (int) $attempt->score) }}</p>
                <p class="text-xs text-red-500 mt-0.5">Wrong</p>
            </div>
            <div class="bg-bg-mid rounded-2xl p-4 text-center">
                <p class="text-2xl font-black text-gray-600">{{ $timeMins }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Time</p>
            </div>
        </div>

        @if($attempt->paper)
            <a href="{{ route('student.competitions.practice.result', $attempt->paper) }}"
               class="block w-full py-3 bg-fran text-white rounded-2xl text-sm font-semibold text-center hover:bg-fran-dark transition-colors">
                View Detailed Report
            </a>
        @endif
    @else
        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
            <div class="text-4xl mb-3">📝</div>
            <p class="font-medium text-gray-600">No result yet</p>
            <p class="text-sm mt-1">Complete a competition practice paper to see your result.</p>
        </div>
    @endif

    <a href="{{ route('student.competitions.index') }}"
       class="block w-full py-3 border border-border text-gray-600 rounded-2xl text-sm font-semibold text-center hover:bg-bg-light transition-colors">
        Back to Home
    </a>

</div>
@endsection
