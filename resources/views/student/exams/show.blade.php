@extends('layouts.student')
@section('title', $exam->title)

@section('content')
<x-student-header :title="$exam->title" :back="route('student.exams.index')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Exam header card --}}
    <div class="bg-white rounded-2xl border border-border p-5 text-center">
        <span class="text-3xl">📅</span>
        <p class="font-black text-gray-900 text-lg mt-1">{{ $exam->title }}</p>
        <div class="flex items-center justify-center gap-3 text-xs text-gray-500 mt-2">
            @if($exam->scheduled_at)
                <span>📅 {{ $exam->scheduled_at->format('d M Y') }}</span>
                <span>🕐 {{ $exam->scheduled_at->format('g:i A') }}</span>
            @endif
        </div>
        <p class="text-xs text-gray-400 mt-1">Scheduled by: <span class="font-semibold text-gray-600">{{ $exam->franchise?->name ?? 'Apex Brains Academy' }}</span></p>
    </div>

    {{-- Stats 2x2 --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-400">⏱️ Duration</p>
            <p class="text-base font-black text-gray-800 mt-1">{{ $exam->duration_minutes }} Minutes</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-400">📝 Questions</p>
            <p class="text-base font-black text-gray-800 mt-1">{{ $exam->total_questions }} MCQ</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-400">🎯 Pass Marks</p>
            <p class="text-base font-black text-gray-800 mt-1">{{ number_format($exam->pass_percentage, 0) }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs text-gray-400">🔄 Attempts</p>
            <p class="text-base font-black text-gray-800 mt-1">{{ $attempts->count() }} of {{ $exam->max_attempts ?? '∞' }}</p>
        </div>
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
                'Audio questions will play automatically.',
                'Submit before the timer ends.',
            ] as $i => $rule)
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-fran-light text-fran text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                    <span class="text-sm text-gray-600">{{ $rule }}</span>
                </li>
            @endforeach
        </ol>
    </div>

    {{-- Action --}}
    @if($inProgress)
        <a href="{{ route('student.exams.attempt', $exam) }}" class="block w-full py-3.5 bg-amber-500 text-white rounded-2xl text-sm font-bold text-center">Resume Attempt →</a>
    @elseif(! $hasPaper)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl py-4 px-5 text-center">
            <p class="text-sm font-bold text-amber-700">Not Available Yet</p>
            <p class="text-xs text-amber-600 mt-1">The question paper hasn't been published yet.</p>
        </div>
    @elseif($exam->scheduled_at && $exam->scheduled_at->isFuture())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl py-4 px-5 text-center">
            <p class="text-sm font-bold text-amber-700">Exam Not Started Yet</p>
            <p class="text-xs text-amber-600 mt-1">Opens on {{ $exam->scheduled_at->format('d M Y \a\t g:i A') }}</p>
        </div>
    @elseif($canAttempt)
        <form method="POST" action="{{ route('student.exams.start', $exam) }}">
            @csrf
            <button type="submit" class="w-full py-3.5 bg-fran text-white rounded-2xl text-sm font-bold">I am Ready — Start Exam</button>
        </form>
    @else
        <div class="py-3 text-center text-sm text-gray-400">No more attempts available.</div>
    @endif

    <a href="{{ route('student.exams.index') }}" class="block w-full py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold text-center">Go Back</a>

</div>
@endsection
