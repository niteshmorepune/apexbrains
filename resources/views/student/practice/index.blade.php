@extends('layouts.student')
@section('title', 'Practice')

@section('content')
@php
    $currentLevelId = $student?->current_level_id ?? optional($levels->first())->id;
@endphp

<div x-data="{ type: '' }">

    {{-- ===== Step 1: type selector (S34) ===== --}}
    <template x-if="type === ''">
        <div>
            <x-student-header title="Practice" :back="route('student.home')" />
            <div class="px-4 pb-4 space-y-3">
                @foreach([
                    ['type' => 'exam',        'emoji' => '📝', 'bg' => 'bg-stu-light',  'title' => 'Exam Practice',        'sub' => 'Evaluate your calculation skills and track your learning progress'],
                    ['type' => 'competition', 'emoji' => '🏆', 'bg' => 'bg-amber-50',   'title' => 'Competition Practice', 'sub' => 'Challenge your abilities and compete with top performers'],
                ] as $opt)
                    <button type="button" @click="type = '{{ $opt['type'] }}'"
                            class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                        <span class="w-12 h-12 rounded-xl {{ $opt['bg'] }} flex items-center justify-center text-2xl flex-shrink-0">{{ $opt['emoji'] }}</span>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-800">{{ $opt['title'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 leading-snug">{{ $opt['sub'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </template>

    {{-- ===== Step 2: setup form (S35 / S38 / S41) ===== --}}
    <template x-if="type !== ''">
        <div>
            <div class="px-4 pt-5 pb-3 flex items-center gap-2">
                <button type="button" @click="type = ''" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900"
                    x-text="{ exam: 'Exam Practice', competition: 'Competition Practice' }[type]"></h1>
            </div>

            <div class="px-4 pb-4 space-y-5">
                @error('level_id')
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ $message }}</div>
                @enderror

                {{-- Pick a difficulty — tapping it starts the session immediately --}}
                <p class="text-sm text-gray-500">Choose a difficulty to begin.</p>
                <form method="POST" action="{{ route('student.practice.start') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="level_id" value="{{ $currentLevelId }}">
                    <input type="hidden" name="count" value="20">
                    <input type="hidden" name="session_length_minutes" value="10">

                    @foreach([
                        ['value' => 'easy',   'emoji' => '🟢', 'title' => 'Easy',   'sub' => 'Build confidence with simpler sums',    'bg' => 'bg-stu-light'],
                        ['value' => 'medium', 'emoji' => '🟡', 'title' => 'Medium', 'sub' => 'Sharpen your speed and accuracy',        'bg' => 'bg-amber-50'],
                        ['value' => 'hard',   'emoji' => '🔴', 'title' => 'Hard',   'sub' => 'Challenge yourself with tougher drills', 'bg' => 'bg-red-50'],
                    ] as $diff)
                        <button type="submit" name="difficulty" value="{{ $diff['value'] }}"
                                class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                            <span class="w-12 h-12 rounded-xl {{ $diff['bg'] }} flex items-center justify-center text-2xl flex-shrink-0">{{ $diff['emoji'] }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-gray-800">{{ $diff['title'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 leading-snug">{{ $diff['sub'] }}</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    @endforeach
                </form>

                {{-- Recent Sessions --}}
                @if($pastSessions->isNotEmpty())
                    <div>
                        <p class="text-sm font-bold text-gray-800 mb-2">Recent Sessions</p>
                        <div class="space-y-2.5">
                            @foreach($pastSessions as $ps)
                                <div class="bg-white rounded-2xl border border-border p-3.5 flex items-center gap-3">
                                    <span class="text-fran">📅</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800">{{ $ps->completed_at?->format('d M, g:i A') ?? 'In progress' }}</p>
                                        <p class="text-xs text-gray-400">{{ $ps->questions_correct ?? 0 }}/{{ $ps->total_questions }} correct · {{ number_format($ps->accuracy ?? 0, 0) }}% accuracy</p>
                                    </div>
                                    <span class="text-base font-black text-stu">{{ number_format($ps->accuracy ?? 0, 0) }}%</span>
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
