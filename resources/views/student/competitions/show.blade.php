@extends('layouts.student')
@section('title', $competition->title)

@section('content')
<x-student-header :title="$competition->title" :back="route('student.competitions.index')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Header card --}}
    <div class="bg-white rounded-2xl border border-border p-5 text-center">
        <span class="text-3xl">🏆</span>
        <p class="font-black text-gray-900 text-lg mt-1">{{ $competition->title }}</p>
        <div class="flex items-center justify-center gap-3 text-xs text-gray-500 mt-2">
            @if($competition->start_date)<span>📅 {{ $competition->start_date_ist->format('d M Y') }}</span>@endif
            @if($competition->start_date)<span>🕐 {{ $competition->start_date_ist->format('h:i A') }}@if($competition->end_date) – {{ $competition->end_date_ist->isSameDay($competition->start_date_ist) ? $competition->end_date_ist->format('h:i A') : $competition->windowEndLabel() }}@endif</span>@endif
        </div>
        @include('partials.competition-exam-status', ['competition' => $competition])
    </div>

    {{-- Stats 2x2 --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4"><p class="text-xs text-gray-400">⏱️ Duration</p><p class="text-base font-black text-gray-800 mt-1">{{ $paper?->duration_minutes ?? 10 }} Minutes</p></div>
        <div class="bg-white rounded-2xl border border-border p-4"><p class="text-xs text-gray-400">📝 Questions</p><p class="text-base font-black text-gray-800 mt-1">{{ $paper?->total_questions ?? '—' }} MCQ</p></div>
        <div class="bg-white rounded-2xl border border-border p-4"><p class="text-xs text-gray-400">🎯 Pass Marks</p><p class="text-base font-black text-gray-800 mt-1">{{ $paper?->pass_percentage ?? 75 }}%</p></div>
        <div class="bg-white rounded-2xl border border-border p-4"><p class="text-xs text-gray-400">🔄 Attempts</p><p class="text-base font-black text-gray-800 mt-1">{{ $myAttempts->count() }} of 3</p></div>
    </div>

    {{-- Rules --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-sm font-bold text-gray-800 mb-3">Rules and Instructions</p>
        <ol class="space-y-2.5">
            @foreach([
                'Do not switch browser tabs — exam will flag.',
                'Ensure stable internet connection.',
                'Answer all questions before time runs out.',
                'Use the question palette to navigate freely.',
                'Submit before the timer ends.',
            ] as $i => $rule)
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-comp-light text-comp text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                    <span class="text-sm text-gray-600">{{ $rule }}</span>
                </li>
            @endforeach
        </ol>
    </div>

    {{-- CTA --}}
    @php
        $examStatus = $competition->examStatus();
        $notStarted = $examStatus === \App\Models\Competition::STATUS_NOT_STARTED;
        $ended      = $examStatus === \App\Models\Competition::STATUS_ENDED;
    @endphp
    @if($registration)
        @if($myAttempts->isEmpty())
            @if($notStarted)
                <div class="bg-comp-light border border-comp/30 rounded-2xl p-4 text-center">
                    <p class="text-sm text-comp font-medium">The exam opens on {{ $competition->windowStartLabel() }} (IST) and closes on {{ $competition->windowEndLabel() }}.</p>
                </div>
            @elseif($ended)
                <div class="bg-comp-light border border-comp/30 rounded-2xl p-4 text-center">
                    <p class="text-sm text-comp font-medium">This competition has ended. The exam closed on {{ $competition->windowEndLabel() }} (IST).</p>
                </div>
            @elseif($paper)
                <form method="POST" action="{{ route('student.competitions.start', $competition) }}">
                    @csrf
                    <button type="submit" class="w-full py-3.5 bg-comp text-white rounded-2xl text-sm font-bold">I am Ready — Start Exam</button>
                </form>
                @if($competition->end_date)
                    <p class="text-center text-xs text-gray-500 -mt-2">Exam closes on {{ $competition->windowEndLabel() }} (IST)</p>
                @endif
            @else
                <div class="bg-comp-light border border-comp/30 rounded-2xl p-4 text-center">
                    <p class="text-sm text-comp font-medium">The question paper for your level is not available yet</p>
                </div>
            @endif
        @else
            <a href="{{ route('student.competitions.result', $competition) }}" class="block w-full py-3.5 bg-comp text-white rounded-2xl text-sm font-bold text-center">View My Result</a>
        @endif
    @else
        <div class="bg-comp-light border border-comp/30 rounded-2xl p-4 text-center">
            <p class="text-sm text-comp font-medium">You are not registered for this competition</p>
        </div>
    @endif

    <a href="{{ route('student.competitions.index') }}" class="block w-full py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold text-center">Go Back</a>

</div>
@endsection
