@extends('layouts.student')
@section('title', 'Exam Result')

@section('content')
@php
    $pct        = (int) round($attempt->percentage);
    $ringColor  = $attempt->is_passed ? '#2ECC71' : '#EF4444';
    $totalQ     = count($attempt->question_ids ?? []);
    $wrongCount = $attempt->answers->where('is_correct', false)->count();
    $timeMins   = $timeTaken > 0 ? floor($timeTaken/60).':'.str_pad($timeTaken%60, 2, '0', STR_PAD_LEFT) : '—';
    $circ       = 2 * 3.14159 * 52;
@endphp

<div class="px-4 pt-6 pb-4 space-y-4">

    {{-- Score ring --}}
    <div class="flex flex-col items-center">
        <div class="relative w-36 h-36">
            <svg class="w-36 h-36 -rotate-90" viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="52" fill="none" stroke="#EDF0F5" stroke-width="10"/>
                <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $ringColor }}" stroke-width="10" stroke-linecap="round"
                        stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ - ($circ * $pct / 100) }}"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <p class="text-3xl font-black text-gray-900">{{ $pct }}%</p>
                <p class="text-xs text-gray-400">Score</p>
            </div>
        </div>
        <p class="text-base font-bold text-gray-800 mt-3">{{ $exam->title }}</p>
        <span class="inline-block font-bold text-xs px-4 py-1 rounded-full mt-1.5 {{ $attempt->is_passed ? 'bg-stu text-white' : 'bg-red-500 text-white' }}">
            {{ $attempt->is_passed ? 'PASSED' : 'FAILED' }}
        </span>
        @if($classAvg)
            <p class="text-xs text-gray-400 mt-1.5">Class Avg: {{ number_format($classAvg, 0) }}%</p>
        @endif
    </div>

    {{-- Stats 2x2 --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3">
            <span class="text-stu text-xl">✅</span>
            <div><p class="text-lg font-black text-stu leading-none">{{ $attempt->score }}/{{ $totalQ }}</p><p class="text-xs text-gray-400 mt-1">Correct</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3">
            <span class="text-red-500 text-xl">❌</span>
            <div><p class="text-lg font-black text-red-500 leading-none">{{ $wrongCount }}/{{ $totalQ }}</p><p class="text-xs text-gray-400 mt-1">Wrong</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3">
            <span class="text-logo-amber text-xl">⏭️</span>
            <div><p class="text-lg font-black text-logo-amber leading-none">{{ $skipped }}/{{ $totalQ }}</p><p class="text-xs text-gray-400 mt-1">Skipped</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3">
            <span class="text-red-500 text-xl">⏱️</span>
            <div><p class="text-lg font-black text-gray-700 leading-none">{{ $timeMins }}</p><p class="text-xs text-gray-400 mt-1">Time Taken</p></div>
        </div>
    </div>

    {{-- Question-by-Question Breakdown --}}
    @if($attempt->answers->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border"><p class="text-sm font-bold text-gray-800">Question-by-Question Breakdown</p></div>
            <div class="divide-y divide-border">
                @foreach($attempt->answers as $answer)
                    <div class="px-4 py-3 flex items-start gap-3">
                        <span class="text-sm mt-0.5">{{ $answer->is_correct ? '✅' : '❌' }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 truncate">{{ $answer->question?->question_text }}</p>
                            <p class="text-xs mt-0.5 {{ $answer->is_correct ? 'text-gray-400' : 'text-red-500' }}">
                                Your: {{ strtoupper($answer->selected_answer) }}) {{ $answer->question?->{'option_' . $answer->selected_answer} }}
                                @unless($answer->is_correct)
                                    · <span class="text-stu">Correct: {{ strtoupper($answer->question?->correct_answer) }})</span>
                                @endunless
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="space-y-3 pt-1">
        <a href="{{ route('student.home') }}" class="block w-full py-3.5 bg-fran text-white rounded-2xl text-sm font-bold text-center">Back to Home</a>
        <a href="{{ route('student.exams.index') }}" class="block w-full py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold text-center">All Exams</a>
    </div>

</div>
@endsection
