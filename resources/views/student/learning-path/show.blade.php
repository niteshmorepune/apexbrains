@extends('layouts.student')
@section('title', $level->title)

@section('content')
@php
    $levelColors = [
        1=>'#87CEEB', 2=>'#2ECC71', 3=>'#00BCD4', 4=>'#FFD54F', 5=>'#F5A623',
        6=>'#FF69B4', 7=>'#D42B2B', 8=>'#9C27B0', 9=>'#1A73E8', 10=>'#00897B',
        11=>'#FF6F00', 12=>'#AD1457', 13=>'#283593', 14=>'#212121',
    ];
    $color = $levelColors[$level->number] ?? '#2ECC71';
@endphp

{{-- Header (left-aligned with back) --}}
<div class="px-4 pt-5 pb-3">
    <a href="{{ route('student.learning-path') }}" class="w-8 h-8 -ml-1 mb-1 flex items-center justify-center text-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div class="flex items-center gap-2">
        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $color }}"></span>
        <h1 class="text-lg font-black text-gray-900">{{ $level->title }}</h1>
    </div>
    @if($level->description)
        <p class="text-xs text-gray-400 mt-1">{{ $level->description }}</p>
    @endif
</div>

<div class="px-4 pb-4 space-y-4">

    @if($level->learning_objectives && count($level->learning_objectives) > 0)
        @php
            $totalTopics    = count($level->learning_objectives);
            $passedCount    = $myAttempts->where('is_passed', true)->count();
            $doneTopics     = min($passedCount, $totalTopics);
            $pct            = $totalTopics > 0 ? round($doneTopics / $totalTopics * 100) : 0;
            $neededForExam  = (int) ceil($totalTopics * 0.8);
            $examUnlocked   = $doneTopics >= $neededForExam;
        @endphp

        {{-- Level Progress --}}
        <div class="bg-white rounded-2xl border border-border p-4">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-bold text-gray-800">Level Progress</p>
                <span class="text-sm font-bold text-stu">{{ $pct }}%</span>
            </div>
            <div class="h-2 bg-bg-mid rounded-full overflow-hidden mb-2">
                <div class="h-full rounded-full transition-all" style="width: {{ max(3, $pct) }}%; background-color: {{ $color }}"></div>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-400">{{ $doneTopics }} of {{ $totalTopics }} topics · Exam:
                    @if($examUnlocked)
                        <span class="text-stu font-semibold">Unlocked</span>
                    @else
                        <span class="text-logo-amber font-semibold">Locked (need {{ $neededForExam }}/{{ $totalTopics }})</span>
                    @endif
                </span>
            </div>
        </div>

        {{-- Topic Checklist --}}
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-sm font-bold text-gray-800 mb-3">Topic Checklist</p>
            <ul class="space-y-2.5">
                @foreach($level->learning_objectives as $i => $obj)
                    @php $done = $i < $doneTopics; $locked = !$done && $i > $doneTopics; @endphp
                    <li class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ $done ? 'bg-stu-light' : 'bg-bg-light' }}">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold
                            {{ $done ? 'bg-stu text-white' : ($locked ? 'bg-bg-mid text-gray-300' : 'border-2 border-gray-300 text-transparent') }}">
                            {{ $done ? '✓' : ($locked ? '·' : '') }}
                        </span>
                        <span class="text-sm {{ $locked ? 'text-gray-300' : 'text-gray-700' }}">{{ $obj }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Exams for this level --}}
    @if($exams->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border">
                <p class="text-sm font-bold text-gray-800">Exams for this Level</p>
            </div>
            <div class="divide-y divide-border">
                @foreach($exams as $exam)
                    @php $myAttempt = $myAttempts->where('exam_id', $exam->id)->first(); @endphp
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $exam->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $exam->total_questions }}Q · {{ $exam->duration_minutes }}min</p>
                        </div>
                        @if($myAttempt)
                            <span class="text-xs px-2 py-1 rounded-full font-bold {{ $myAttempt->is_passed ? 'text-stu bg-stu-light' : 'text-red-500 bg-red-50' }}">{{ number_format($myAttempt->percentage, 0) }}%</span>
                        @else
                            <a href="{{ route('student.exams.show', $exam) }}" class="text-xs bg-fran text-white px-3 py-1.5 rounded-lg font-semibold">Take →</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Continue Practice --}}
    @if($student?->current_level_id === $level->id)
        <a href="{{ route('student.practice.index') }}"
           class="block bg-fran text-white text-center font-bold py-3.5 rounded-2xl text-sm">
            Continue Practice
        </a>
    @endif

</div>
@endsection
