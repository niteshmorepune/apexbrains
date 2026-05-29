@extends('layouts.franchise')
@section('title', 'New Class Practice Session')
@section('page-title', 'New Class Practice Session')

@section('page-actions')
    <a href="{{ route('franchise.class-practice.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Back
    </a>
@endsection

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-border p-6">
        <h2 class="text-sm font-bold text-fran mb-5">Session Setup</h2>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('franchise.class-practice.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Session Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="100"
                       placeholder="e.g. Friday Level 3 Practice"
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Level <span class="text-red-500">*</span></label>
                <select name="level_id" required
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    <option value="">Select Level</option>
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>
                            Level {{ $level->number }}@if($level->title) — {{ $level->title }}@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Time Per Step <span class="text-red-500">*</span></label>
                <div class="flex gap-3">
                    @foreach(['2' => '2s', '3' => '3s', '5' => '5s', '10' => '10s', '30' => '30s'] as $val => $lbl)
                        <label class="cursor-pointer flex-1">
                            <input type="radio" name="time_per_question_seconds" value="{{ $val }}"
                                   {{ old('time_per_question_seconds', '5') === $val ? 'checked' : '' }} class="sr-only peer">
                            <span class="block text-center py-2 rounded-xl border text-sm font-medium transition-colors
                                         peer-checked:bg-fran peer-checked:text-white peer-checked:border-fran
                                         border-border text-gray-600 hover:border-fran">{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Number of Questions</label>
                <input type="number" name="total_questions" value="{{ old('total_questions', 150) }}"
                       min="1" max="300" required
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>

            {{-- Audio Dictation toggle --}}
            <div class="flex items-center justify-between bg-bg-light rounded-xl p-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">Audio Dictation</p>
                    <p class="text-xs text-gray-400 mt-0.5">Play voice for each number automatically</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="audio_dictation" value="0">
                    <input type="checkbox" name="audio_dictation" value="1" class="sr-only peer"
                           {{ old('audio_dictation') ? 'checked' : '' }}>
                    <div class="w-10 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-5 peer-checked:bg-fran after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                </label>
            </div>

            {{-- Session Length --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Session Length</label>
                <div class="flex gap-3">
                    @foreach(['8' => '8 min', '10' => '10 min', '15' => '15 min', '20' => '20 min', '0' => 'Unlimited'] as $val => $lbl)
                        <label class="cursor-pointer flex-1">
                            <input type="radio" name="session_length_minutes" value="{{ $val }}"
                                   {{ old('session_length_minutes', '10') === $val ? 'checked' : '' }} class="sr-only peer">
                            <span class="block text-center py-2 rounded-xl border text-xs font-medium transition-colors
                                         peer-checked:bg-fran peer-checked:text-white peer-checked:border-fran
                                         border-border text-gray-600 hover:border-fran">{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                Next Question →
            </button>
        </form>
    </div>
</div>

@endsection
