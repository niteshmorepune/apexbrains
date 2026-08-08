@extends('layouts.student')
@section('title', 'Practice Result')

@section('content')
@php
    $total = count($attempt->question_ids ?? []);
    $stars = match(true) {
        $attempt->percentage <= 30 => 1,
        $attempt->percentage <= 50 => 2,
        $attempt->percentage <= 70 => 3,
        $attempt->percentage <= 90 => 4,
        default => 5,
    };
    $motivation = match(true) {
        $attempt->percentage <= 30 => 'Keep practicing — you\'ll get there!',
        $attempt->percentage <= 50 => 'Good effort — a little more practice will help.',
        $attempt->percentage <= 70 => 'Well done! You\'re getting the hang of it.',
        $attempt->percentage <= 90 => 'Great job! Almost perfect.',
        default => 'Outstanding! Keep up the excellent work.',
    };
@endphp
<div class="p-4 space-y-4">

    {{-- Score --}}
    <div class="bg-fran rounded-2xl p-6 text-white text-center">
        <p class="text-white/70 text-sm mb-1">{{ $attempt->level?->title }} Competition Practice</p>
        <p class="text-4xl font-black mb-1">{{ $attempt->score }}/{{ $total }}</p>
        <p class="text-white/70 text-sm">Total Marks</p>
        <p class="text-lg mt-2 tracking-widest">{{ str_repeat('★', $stars) }}{{ str_repeat('☆', 5 - $stars) }}</p>
        <p class="text-white/90 text-sm font-medium mt-1">{{ $motivation }}</p>

        <div class="grid grid-cols-3 gap-3 mt-4">
            <div class="bg-white/10 rounded-xl p-3">
                <p class="font-black text-lg">{{ $attempt->score }}</p>
                <p class="text-white/60 text-xs">Correct</p>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <p class="font-black text-lg">{{ count($attempt->question_ids ?? []) - $attempt->score }}</p>
                <p class="text-white/60 text-xs">Wrong</p>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <p class="font-black text-lg">
                    {{ $attempt->started_at && $attempt->submitted_at ? gmdate('i:s', (int) $attempt->submitted_at->diffInSeconds($attempt->started_at, true)) : '—' }}
                </p>
                <p class="text-white/60 text-xs">Time Taken</p>
            </div>
        </div>
    </div>

    {{-- Answer review --}}
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border">
            <p class="text-sm font-semibold text-gray-700">Answer Key</p>
        </div>
        <div class="divide-y divide-border">
            @foreach($questions as $i => $q)
                @php
                    $correct = strtolower($q->correct_answer);
                    $selected = strtolower($attempt->answers[$q->id] ?? '') ?: null;
                    $isCorrect = $selected === $correct;
                @endphp
                <div class="px-4 py-3">
                    <div class="flex items-start gap-2 mb-2">
                        <span class="text-xs text-gray-400 flex-shrink-0 mt-0.5">{{ $i + 1 }}.</span>
                        <p class="text-sm text-gray-800 flex-1">{{ $q->question_text }}</p>
                        <span class="text-xs font-semibold flex-shrink-0 {{ $isCorrect ? 'text-green-600' : 'text-red-500' }}">
                            {{ $isCorrect ? '✓ Correct' : ($selected ? '✗ Wrong' : '— Skipped') }}
                        </span>
                    </div>
                    <div class="ml-4 text-xs space-y-1">
                        @if($selected && ! $isCorrect)
                            <p class="text-red-500 font-medium">
                                Your Answer: {{ strtoupper($selected) }}) {{ $q->{'option_' . $selected} }}
                            </p>
                        @endif
                        <p class="text-green-600 font-medium">
                            Correct: {{ strtoupper($correct) }}) {{ $q->{'option_' . $correct} }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="space-y-3">
        <a href="{{ route('student.competitions.practice') }}"
           class="block w-full py-3 border border-border text-gray-700 rounded-2xl text-sm font-semibold text-center">
            ← Back to Competition Practice
        </a>
        <form method="POST" action="{{ route('student.competitions.practice.start') }}">
            @csrf
            <button type="submit"
                    class="w-full py-3 bg-fran text-white rounded-2xl text-sm font-semibold">
                Try Again
            </button>
        </form>
    </div>

</div>
@endsection
