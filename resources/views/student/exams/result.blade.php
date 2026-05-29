@extends('layouts.student')
@section('title', 'Exam Result')

@section('content')
<div class="p-4 space-y-4">

    {{-- Result banner --}}
    <div class="rounded-2xl p-6 text-white text-center
        {{ $attempt->is_passed ? 'bg-stu' : 'bg-red-500' }}">
        <p class="text-white/80 text-sm">{{ $exam->title }}</p>
        @if($classAvg)
            <p class="text-white/60 text-xs mt-0.5">Class Avg: {{ number_format($classAvg, 0) }}%</p>
        @endif
        <p class="text-5xl font-black my-2">{{ number_format($attempt->percentage, 0) }}%</p>
        <span class="inline-block font-bold text-sm px-4 py-1 rounded-full
            {{ $attempt->is_passed ? 'bg-white/20 text-white' : 'bg-white text-red-500' }}">
            {{ $attempt->is_passed ? 'PASSED' : 'FAILED' }}
        </span>
    </div>

    {{-- 4-stat grid --}}
    @php
        $timeMins = $timeTaken > 0 ? floor($timeTaken/60).':'.str_pad($timeTaken%60, 2, '0', STR_PAD_LEFT) : '—';
        $wrongCount = $attempt->answers->where('is_correct', false)->count();
    @endphp
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-green-50 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-green-600">{{ $attempt->score }}</p>
            <p class="text-xs text-green-600 mt-0.5">Correct</p>
        </div>
        <div class="bg-red-50 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-red-500">{{ $wrongCount }}</p>
            <p class="text-xs text-red-500 mt-0.5">Wrong</p>
        </div>
        <div class="bg-yellow-50 rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-logo-amber">{{ $skipped }}</p>
            <p class="text-xs text-logo-amber mt-0.5">Skipped</p>
        </div>
        <div class="bg-bg-mid rounded-2xl p-4 text-center">
            <p class="text-2xl font-black text-gray-600">{{ $timeMins }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Time Taken</p>
        </div>
    </div>

    {{-- Review Incorrect --}}
    @if($wrongCount > 0)
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border">
            <p class="text-sm font-semibold text-gray-700">Review Incorrect ({{ $wrongCount }})</p>
        </div>
        <div class="divide-y divide-border">
            @foreach($attempt->answers->where('is_correct', false) as $i => $answer)
                <div class="px-4 py-3">
                    <p class="text-sm text-gray-800 mb-2 leading-snug">{{ $answer->question?->question_text }}</p>
                    <p class="text-xs text-red-500">
                        ✗ Your answer: {{ strtoupper($answer->selected_answer) }}) {{ $answer->question?->{'option_' . $answer->selected_answer} }}
                    </p>
                    <p class="text-xs text-green-600 font-medium mt-0.5">
                        ✓ Correct: {{ strtoupper($answer->question?->correct_answer) }}) {{ $answer->question?->{'option_' . $answer->question?->correct_answer} }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="space-y-3">
        <a href="{{ route('student.exams.index') }}"
           class="block w-full py-3 border border-border text-gray-700 rounded-2xl text-sm font-semibold text-center hover:bg-bg-light">
            ← Back to Exams
        </a>
        <a href="{{ route('student.home') }}"
           class="block w-full py-3 bg-fran text-white rounded-2xl text-sm font-semibold text-center">
            Home
        </a>
    </div>

</div>
@endsection
