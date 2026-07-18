@extends('layouts.external')
@section('title', 'Practice Result')

@section('content')
<x-student-header title="Practice Result" :back="route('external.practice.index')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Score --}}
    <div class="bg-fran rounded-2xl p-6 text-white text-center">
        <p class="text-white/70 text-sm mb-1">{{ $attempt->level?->title }} Competition Practice</p>
        <p class="text-4xl font-black mb-1">{{ number_format($attempt->percentage, 0) }}%</p>
        <p class="text-white/70 text-sm">{{ $attempt->score }} of {{ count($attempt->question_ids ?? []) }} correct</p>

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
                @php $correct = strtolower($q->correct_answer); @endphp
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

    <div class="grid grid-cols-2 gap-3 pt-1">
        <a href="{{ route('external.practice.index') }}" class="block text-center py-3.5 bg-fran text-white rounded-2xl text-sm font-bold">Practice Again</a>
        <a href="{{ route('external.results') }}" class="block text-center py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold">All Results</a>
    </div>

</div>
@endsection
