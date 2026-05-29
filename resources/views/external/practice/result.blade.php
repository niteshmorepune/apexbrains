@extends('layouts.external')
@section('title', 'Result — ' . $paper->title)

@section('content')
<div class="p-4 space-y-4">

    {{-- Score --}}
    <div class="bg-fran rounded-2xl p-6 text-white text-center">
        <p class="text-white/70 text-sm mb-1">{{ $paper->title }}</p>
        <p class="text-5xl font-black mb-1">{{ $attempt->score }}/{{ $paper->total_questions }}</p>
        <p class="text-white/70 text-sm">Correct</p>
    </div>

    {{-- 3 stat chips --}}
    @php
        $durationSec = $attempt->started_at && $attempt->submitted_at
            ? $attempt->submitted_at->diffInSeconds($attempt->started_at) : 0;
        $avgSpeed = $paper->total_questions > 0 ? round($durationSec / $paper->total_questions, 1) : null;
        $accuracy = $paper->total_questions > 0 ? round($attempt->score / $paper->total_questions * 100) : 0;
    @endphp
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-lg font-bold text-stu">{{ $accuracy }}%</p>
            <p class="text-xs text-gray-400 mt-0.5">Accuracy</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-lg font-bold text-fran">{{ $avgSpeed ? $avgSpeed.'s' : '—' }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Avg Speed</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-lg font-bold text-logo-amber">{{ (int) ceil($durationSec / 60) }}m</p>
            <p class="text-xs text-gray-400 mt-0.5">Duration</p>
        </div>
    </div>

    {{-- Review Incorrect --}}
    @php
        $wrongAnswers = $attempt->answers ?? collect();
        $incorrectCount = is_array($attempt->answers) ? collect($attempt->answers)->where('is_correct', false)->count() : 0;
    @endphp
    @if($incorrectCount > 0 || $questions->isNotEmpty())
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border">
            <p class="text-sm font-semibold text-gray-700">
                Review Incorrect {{ $incorrectCount > 0 ? "($incorrectCount)" : '' }}
            </p>
        </div>
        <div class="divide-y divide-border">
            @foreach($questions as $i => $pq)
                @php
                    $q = $pq->question;
                    $correct = strtolower($q->correct_answer ?? '');
                    $myAnswer = is_array($attempt->answers) ? (collect($attempt->answers)->firstWhere('question_id', $q->id)['answer'] ?? null) : null;
                    $isWrong = $myAnswer && strtolower($myAnswer) !== $correct;
                @endphp
                @if($isWrong)
                    <div class="px-4 py-3">
                        <div class="flex items-start gap-2 mb-1.5">
                            <span class="text-xs text-gray-400 flex-shrink-0 mt-0.5">{{ $i + 1 }}.</span>
                            <p class="text-sm text-gray-800 flex-1 leading-snug">{{ $q->question_text }}</p>
                        </div>
                        <p class="ml-4 text-xs text-red-500">✗ Your answer: {{ strtoupper($myAnswer) }})</p>
                        <p class="ml-4 text-xs text-green-600 font-medium mt-0.5">
                            ✓ Correct: {{ strtoupper($correct) }}) {{ $q->{'option_' . $correct} ?? '' }}
                        </p>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('external.practice.start', $paper) }}">
            @csrf
            <button type="submit" class="w-full py-3 bg-fran text-white rounded-2xl text-sm font-semibold">
                Try Again
            </button>
        </form>
        <a href="{{ route('external.practice.index') }}"
           class="block w-full py-3 border border-border text-gray-600 rounded-2xl text-sm font-semibold text-center">
            ← All Papers
        </a>
    </div>

</div>
@endsection
