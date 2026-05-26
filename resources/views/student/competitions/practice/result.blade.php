@extends('layouts.student')
@section('title', 'Paper Result')

@section('content')
<div class="p-4 space-y-4">

    {{-- Score --}}
    <div class="bg-fran rounded-2xl p-6 text-white text-center">
        <p class="text-white/70 text-sm mb-1">{{ $paper->title }}</p>
        <p class="text-4xl font-black mb-1">{{ number_format($attempt->percentage, 0) }}%</p>
        <p class="text-white/70 text-sm">{{ $attempt->score }} of {{ $paper->total_questions }} correct</p>

        <div class="grid grid-cols-3 gap-3 mt-4">
            <div class="bg-white/10 rounded-xl p-3">
                <p class="font-black text-lg">{{ $attempt->score }}</p>
                <p class="text-white/60 text-xs">Correct</p>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <p class="font-black text-lg">{{ $paper->total_questions - $attempt->score }}</p>
                <p class="text-white/60 text-xs">Wrong</p>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <p class="font-black text-lg">{{ $paper->duration_minutes }}m</p>
                <p class="text-white/60 text-xs">Duration</p>
            </div>
        </div>
    </div>

    {{-- Answer review --}}
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border">
            <p class="text-sm font-semibold text-gray-700">Answer Key</p>
        </div>
        <div class="divide-y divide-border">
            @foreach($questions as $i => $pq)
                @php
                    $q = $pq->question;
                    $correct = strtolower($q->correct_answer);
                @endphp
                <div class="px-4 py-3">
                    <div class="flex items-start gap-2 mb-2">
                        <span class="text-xs text-gray-400 flex-shrink-0 mt-0.5">{{ $i + 1 }}.</span>
                        <p class="text-sm text-gray-800 flex-1">{{ $q->question_text }}</p>
                    </div>
                    <div class="ml-4 text-xs">
                        <p class="text-green-600 font-medium">
                            Correct: {{ strtoupper($correct) }}) {{ $q->{'option_' . $correct} }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="space-y-3">
        <a href="{{ route('student.competitions.practice.index') }}"
           class="block w-full py-3 border border-border text-gray-700 rounded-2xl text-sm font-semibold text-center">
            ← All Papers
        </a>
        <form method="POST" action="{{ route('student.competitions.practice.start', $paper) }}">
            @csrf
            <button type="submit"
                    class="w-full py-3 bg-fran text-white rounded-2xl text-sm font-semibold">
                Try Again
            </button>
        </form>
    </div>

</div>
@endsection
