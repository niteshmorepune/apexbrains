@extends('layouts.student')
@section('title', 'Level ' . $level->number)

@section('subheader')
    <p class="text-white/80 text-xs">
        Level {{ $level->number }}@if($level->title) — {{ $level->title }}@endif
    </p>
@endsection

@section('content')
<div class="p-4 space-y-4">

    {{-- Level header --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-stu flex items-center justify-center text-white font-black text-xl flex-shrink-0">
                {{ $level->number }}
            </div>
            <div class="flex-1">
                <p class="font-bold text-gray-800">Level {{ $level->number }}</p>
                @if($level->title)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $level->title }}</p>
                @endif
                @if($level->description)
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">{{ $level->description }}</p>
                @endif
            </div>
        </div>

        @if($student?->current_level_id === $level->id)
            <div class="mt-3 pt-3 border-t border-border">
                <span class="text-xs bg-stu text-white px-3 py-1 rounded-full font-medium">Your current level</span>
            </div>
        @elseif($currentLevelNumber >= $level->number)
            <div class="mt-3 pt-3 border-t border-border">
                <span class="text-xs bg-green-50 text-green-600 px-3 py-1 rounded-full font-medium">Completed</span>
            </div>
        @endif
    </div>

    {{-- Level Progress --}}
    @if($level->learning_objectives && count($level->learning_objectives) > 0)
        @php
            $totalTopics    = count($level->learning_objectives);
            $passedCount    = $myAttempts->where('is_passed', true)->count();
            $doneTopics     = min($passedCount, $totalTopics);
            $pct            = $totalTopics > 0 ? round($doneTopics / $totalTopics * 100) : 0;
            $examUnlocked   = $doneTopics >= (int) ceil($totalTopics * 0.8);
            $neededForExam  = (int) ceil($totalTopics * 0.8);
        @endphp
        <div class="bg-white rounded-2xl border border-border p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-bold text-gray-700">Level Progress</p>
                <span class="text-sm font-bold text-stu">{{ $pct }}%</span>
            </div>
            <div class="h-2 bg-bg-mid rounded-full mb-2">
                <div class="h-full bg-stu rounded-full transition-all" style="width: {{ max(3, $pct) }}%"></div>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500">{{ $doneTopics }} of {{ $totalTopics }} topics</span>
                @if($examUnlocked)
                    <span class="text-stu font-medium">Exam: Unlocked ✓</span>
                @else
                    <span class="text-logo-amber font-medium">Exam: Locked (need {{ $neededForExam }}/{{ $totalTopics }})</span>
                @endif
            </div>
        </div>

        {{-- Topic Checklist --}}
        <div class="bg-white rounded-2xl border border-border p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Syllabus Topics</p>
            <ul class="space-y-2.5">
                @foreach($level->learning_objectives as $i => $obj)
                    @php
                        $done   = $i < $doneTopics;
                        $locked = !$done && $i > $doneTopics;
                    @endphp
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-xs mt-0.5
                            {{ $done ? 'bg-stu text-white' : ($locked ? 'bg-bg-mid text-gray-300' : 'border-2 border-stu text-transparent') }}">
                            {{ $done ? '✓' : ($locked ? '🔒' : '○') }}
                        </span>
                        <span class="text-sm {{ $locked ? 'text-gray-300' : 'text-gray-700' }}">{{ $obj }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Available Exams --}}
    @if($exams->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border">
                <p class="text-sm font-semibold text-gray-700">Exams for this Level</p>
            </div>
            <div class="divide-y divide-border">
                @foreach($exams as $exam)
                    @php
                        $myAttempt = $myAttempts->where('exam_id', $exam->id)->first();
                    @endphp
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $exam->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $exam->total_questions }}Q · {{ $exam->duration_minutes }}min
                                @if($exam->scheduled_at)
                                    · {{ $exam->scheduled_at->format('d M') }}
                                @endif
                            </p>
                        </div>
                        @if($myAttempt)
                            <span class="text-xs {{ $myAttempt->is_passed ? 'text-green-600 bg-green-50' : 'text-red-500 bg-red-50' }} px-2 py-1 rounded-full font-medium">
                                {{ number_format($myAttempt->percentage, 0) }}%
                            </span>
                        @else
                            <a href="{{ route('student.exams.show', $exam) }}"
                               class="text-xs bg-fran text-white px-3 py-1.5 rounded-lg font-medium">
                                Take →
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Attempt History --}}
    @if($myAttempts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border">
                <p class="text-sm font-semibold text-gray-700">Your Attempts</p>
            </div>
            <div class="divide-y divide-border">
                @foreach($myAttempts as $attempt)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                            <span class="text-xs font-bold">{{ $attempt->is_passed ? '✓' : '✗' }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-700">{{ $attempt->exam?->title }}</p>
                            <p class="text-xs text-gray-400">{{ $attempt->submitted_at?->format('d M Y') }}</p>
                        </div>
                        <span class="font-bold text-sm {{ $attempt->is_passed ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($attempt->percentage, 0) }}%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($student?->current_level_id === $level->id)
        <a href="{{ route('student.practice.index') }}"
           class="block bg-stu text-white text-center font-bold py-3.5 rounded-2xl text-sm hover:bg-stu-dark transition-colors">
            Continue Practice
        </a>
    @endif

    <a href="{{ route('student.learning-path') }}"
       class="block text-center text-sm text-gray-400 hover:text-gray-600 py-2">
        ← Back to My Learning
    </a>

</div>
@endsection
