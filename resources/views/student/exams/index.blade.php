@extends('layouts.student')
@section('title', 'Exams')

@section('content')
@php
    $borderColors = ['#D42B2B', '#F5A623', '#FFD54F', '#FF69B4', '#1A73E8', '#9C27B0'];
@endphp

<div x-data="{ view: 'menu' }">

    {{-- ===== S44: type selector ===== --}}
    <template x-if="view === 'menu'">
        <div>
            <x-student-header title="My Exams" :back="route('student.home')" />
            <div class="px-4 pb-4 space-y-3">
                <button type="button" @click="view = 'exam'"
                        class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-stu-light flex items-center justify-center text-2xl flex-shrink-0">📝</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Exam</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Evaluate your calculation skills and track your learning progress</p>
                    </div>
                </button>
                <a href="{{ route('student.competitions.index') }}"
                   class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-2xl flex-shrink-0">🏆</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Competition</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Challenge your abilities and compete with top performers</p>
                    </div>
                </a>
            </div>
        </div>
    </template>

    {{-- ===== S45: exam list ===== --}}
    <template x-if="view === 'exam'">
        <div>
            <div class="px-4 pt-5 pb-3 flex items-center gap-2">
                <button type="button" @click="view = 'menu'" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900">Exam</h1>
            </div>

            <div class="px-4 pb-4 space-y-4">
                {{-- Upcoming --}}
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Upcoming Exams</p>
                    @forelse($upcomingExams as $exam)
                        <a href="{{ route('student.exams.show', $exam) }}" class="block bg-white rounded-2xl border border-border p-4 mb-3">
                            <div class="flex items-start gap-3">
                                <span class="w-10 h-10 rounded-xl bg-fran-light flex items-center justify-center text-fran flex-shrink-0">📅</span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 text-sm">{{ $exam->title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        @if($exam->scheduled_at){{ $exam->scheduled_at->format('d M Y · g:i A') }} · @endif{{ $exam->total_questions }} Questions
                                    </p>
                                </div>
                                <span class="text-[11px] bg-bg-mid text-gray-500 px-2.5 py-1 rounded-full font-medium flex-shrink-0">Registered</span>
                            </div>
                        </a>
                    @empty
                        <div class="bg-white rounded-2xl border border-border p-6 text-center text-sm text-gray-400">No upcoming exams scheduled.</div>
                    @endforelse
                </div>

                {{-- Past --}}
                @if($pastAttempts->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Past Exams</p>
                        <div class="space-y-3">
                            @foreach($pastAttempts as $i => $attempt)
                                <div class="bg-white rounded-2xl border border-border overflow-hidden flex items-stretch">
                                    <span class="w-1.5 flex-shrink-0" style="background-color: {{ $borderColors[$i % count($borderColors)] }}"></span>
                                    <div class="flex items-center gap-3 p-4 flex-1 min-w-0">
                                        <span class="w-10 h-10 rounded-full bg-bg-light flex items-center justify-center flex-shrink-0">📊</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-800 truncate">{{ $attempt->exam?->title }}</p>
                                            <p class="text-xs text-gray-400">{{ $attempt->submitted_at?->format('d M Y') }}</p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $attempt->is_passed ? 'text-stu bg-stu-light' : 'text-red-500 bg-red-50' }}">{{ $attempt->is_passed ? 'Pass' : 'Fail' }} {{ number_format($attempt->percentage, 0) }}%</span>
                                            <a href="{{ route('student.exams.result', $attempt->exam) }}" class="block text-xs text-fran font-semibold mt-1">View Report</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </template>

</div>
@endsection
