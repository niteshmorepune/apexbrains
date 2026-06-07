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
                    ['type' => 'class',       'emoji' => '📘', 'bg' => 'bg-fran-light', 'title' => 'Class Practice',       'sub' => 'Build speed, accuracy, and confidence through guided exercises'],
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
                    x-text="{ class: 'Class Practice', exam: 'Exam Practice', competition: 'Competition Practice' }[type]"></h1>
            </div>

            <div class="px-4 pb-4 space-y-5">
                @error('level_id')
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ $message }}</div>
                @enderror

                <form method="POST" action="{{ route('student.practice.start') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="practice_type" :value="type">
                    <input type="hidden" name="level_id" value="{{ $currentLevelId }}">

                    {{-- Number of Questions --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Number of Questions</label>
                        <select name="count" class="w-full bg-white border border-border rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-fran">
                            @foreach([50, 100, 120, 150] as $c)
                                <option value="{{ $c }}" @selected($c === 150)>{{ $c }} Questions</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Time per steps --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Time per steps</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['2' => '2 Sec', '2.5' => '2.5 Sec', '3' => '3 Sec'] as $v => $l)
                                <label class="cursor-pointer">
                                    <input type="radio" name="time_per_question_seconds" value="{{ $v }}" {{ $v === '2.5' ? 'checked' : '' }} class="sr-only peer">
                                    <span class="block text-center py-3 rounded-xl border bg-white text-sm font-semibold text-gray-600
                                                 peer-checked:border-fran peer-checked:text-fran peer-checked:bg-fran-light border-border">{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Session Length --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Session Length</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['8' => '8 min', '10' => '10 min'] as $v => $l)
                                <label class="cursor-pointer">
                                    <input type="radio" name="session_length_minutes" value="{{ $v }}" {{ $v === '10' ? 'checked' : '' }} class="sr-only peer">
                                    <span class="block text-center py-3 rounded-xl border bg-white text-sm font-semibold text-gray-600
                                                 peer-checked:border-fran peer-checked:text-fran peer-checked:bg-fran-light border-border">{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Audio Dictation --}}
                    <div class="flex items-center justify-between bg-white border border-border rounded-xl px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Audio Dictation</p>
                            <p class="text-xs text-gray-400">Play voice for each number automatically</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="audio_dictation" value="0">
                            <input type="checkbox" name="audio_dictation" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-fran after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-fran text-white rounded-xl text-sm font-bold">Start Practice Session</button>
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
