@extends('layouts.student')
@section('title', 'Exams')

@section('content')
<div class="p-4 space-y-4" x-data="{ tab: 'exam' }">

    {{-- Type tabs --}}
    <div class="flex gap-2">
        <button @click="tab = 'exam'"
                :class="tab === 'exam' ? 'bg-fran text-white' : 'bg-white border border-border text-gray-600'"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            Exam
        </button>
        <button @click="tab = 'competition'"
                :class="tab === 'competition' ? 'bg-fran text-white' : 'bg-white border border-border text-gray-600'"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            Competition
        </button>
    </div>

    {{-- Exam tab --}}
    <div x-show="tab === 'exam'">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Upcoming Exams</p>
        @forelse($upcomingExams as $exam)
            <a href="{{ route('student.exams.show', $exam) }}"
               class="block bg-white rounded-2xl border border-border p-4 mb-3 hover:border-fran transition-colors">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-fran/10 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-fran font-bold text-xs">L{{ $exam->level?->number }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="font-semibold text-gray-800 text-sm">{{ $exam->title }}</p>
                            <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full">Registered</span>
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ $exam->total_questions }}Q · {{ $exam->duration_minutes }}min
                        </p>
                        @if($exam->scheduled_at)
                            <p class="text-xs text-fran mt-1">{{ $exam->scheduled_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-border p-8 text-center text-gray-400">
                <p class="text-sm">No upcoming exams scheduled.</p>
            </div>
        @endforelse

        @if($pastAttempts->isNotEmpty())
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2 mt-2">Past Exams</p>
            <div class="bg-white rounded-2xl border border-border overflow-hidden">
                <div class="divide-y divide-border">
                    @foreach($pastAttempts as $attempt)
                        <div class="px-4 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                                <span class="text-xs font-bold">{{ $attempt->is_passed ? '✓' : '✗' }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->exam?->title }}</p>
                                <p class="text-xs text-gray-400">{{ $attempt->submitted_at?->format('d M Y') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="text-right">
                                    <p class="font-bold text-sm {{ $attempt->is_passed ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($attempt->percentage, 0) }}%
                                    </p>
                                    <p class="text-xs {{ $attempt->is_passed ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $attempt->is_passed ? 'Pass' : 'Fail' }}
                                    </p>
                                </div>
                                <a href="{{ route('student.exams.result', $attempt->exam) }}"
                                   class="text-xs text-fran hover:underline font-medium">
                                    View Report
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Competition tab --}}
    <div x-show="tab === 'competition'">
        <a href="{{ route('student.competitions.index') }}"
           class="block bg-white rounded-2xl border border-border p-5 text-center hover:border-fran transition-colors">
            <div class="text-3xl mb-2">🏆</div>
            <p class="font-semibold text-gray-700">View Competitions</p>
            <p class="text-xs text-gray-400 mt-1">Register for abacus competitions</p>
        </a>
    </div>

</div>
@endsection
