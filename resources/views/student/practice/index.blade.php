@extends('layouts.student')
@section('title', 'Practice')

@section('content')
<div class="p-4 space-y-4" x-data="{ practiceType: '' }">

    {{-- Type selector --}}
    <template x-if="practiceType === ''">
        <div class="space-y-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Choose Practice Type</p>

            @foreach([
                ['type' => 'exam',        'emoji' => '📝', 'title' => 'Exam Practice',        'sub' => 'Prepare for level assessments'],
                ['type' => 'class',       'emoji' => '🏫', 'title' => 'Class Practice',        'sub' => 'Speed drills for your level'],
                ['type' => 'competition', 'emoji' => '🏆', 'title' => 'Competition Practice',  'sub' => 'Train for abacus competitions'],
            ] as $opt)
                <button type="button" @click="practiceType = '{{ $opt['type'] }}'"
                        class="w-full bg-white rounded-2xl border border-border p-5 text-left hover:border-stu transition-colors flex items-center gap-4">
                    <span class="text-3xl">{{ $opt['emoji'] }}</span>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $opt['title'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $opt['sub'] }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @endforeach
        </div>
    </template>

    {{-- Config form (shown after type selected) --}}
    <template x-if="practiceType !== ''">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <button type="button" @click="practiceType = ''"
                        class="text-stu text-sm">← Back</button>
                <p class="text-sm font-bold text-gray-700"
                   x-text="{ exam: 'Exam Practice', class: 'Class Practice', competition: 'Competition Practice' }[practiceType]"></p>
            </div>

            @error('level_id')
                <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ $message }}</div>
            @enderror

            <div class="bg-white rounded-2xl border border-border p-5">
                <form method="POST" action="{{ route('student.practice.start') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="practice_type" :value="practiceType">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Level</label>
                        <select name="level_id" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                            <option value="">Select level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected($student?->current_level_id === $level->id)>
                                    Level {{ $level->number }}@if($level->title) — {{ $level->title }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Time per step</label>
                        <div class="flex gap-2">
                            @foreach(['2' => '2s', '3' => '3s', '5' => '5s', '10' => '10s', '30' => '30s'] as $v => $l)
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="time_per_question_seconds" value="{{ $v }}"
                                           {{ $v === '5' ? 'checked' : '' }} class="sr-only peer">
                                    <span class="block text-center py-2 rounded-xl border text-xs font-medium
                                                 peer-checked:bg-stu peer-checked:text-white peer-checked:border-stu
                                                 border-border text-gray-600">{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Session Length</label>
                        <div class="flex gap-2">
                            @foreach(['8' => '8 min', '10' => '10 min', '15' => '15 min', '0' => '∞'] as $v => $l)
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="session_length_minutes" value="{{ $v }}"
                                           {{ $v === '10' ? 'checked' : '' }} class="sr-only peer">
                                    <span class="block text-center py-2 rounded-xl border text-xs font-medium
                                                 peer-checked:bg-stu peer-checked:text-white peer-checked:border-stu
                                                 border-border text-gray-600">{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Number of Questions</label>
                        <input type="number" name="count" value="150" min="5" max="300" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                    </div>

                    {{-- Audio Dictation --}}
                    <div class="flex items-center justify-between bg-bg-light rounded-xl p-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Audio Dictation</p>
                            <p class="text-xs text-gray-400">Play voice automatically</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="audio_dictation" value="0">
                            <input type="checkbox" name="audio_dictation" value="1" class="sr-only peer">
                            <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:bg-stu after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full py-3 bg-stu text-white rounded-xl text-sm font-semibold hover:bg-stu-dark transition-colors">
                        Start Practice Session ⚡
                    </button>
                </form>
            </div>

            {{-- Recent Sessions --}}
            @if($pastSessions->isNotEmpty())
                <div class="bg-white rounded-2xl border border-border overflow-hidden">
                    <div class="px-4 py-3 border-b border-border">
                        <p class="text-sm font-semibold text-gray-700">Recent Sessions</p>
                    </div>
                    <div class="divide-y divide-border">
                        @foreach($pastSessions as $ps)
                            <div class="px-4 py-3 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-stu/10 flex items-center justify-center flex-shrink-0">
                                    <span class="text-stu font-bold text-sm">{{ number_format($ps->accuracy, 0) }}%</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800">Level {{ $ps->level?->number }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $ps->questions_correct }}/{{ $ps->total_questions }} correct
                                        · {{ $ps->completed_at?->diffForHumans() }}
                                    </p>
                                </div>
                                <a href="{{ route('student.practice.results', $ps) }}"
                                   class="text-xs text-gray-400 hover:text-gray-600">View</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </template>

</div>
@endsection
