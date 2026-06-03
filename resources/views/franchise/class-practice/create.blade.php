@extends('layouts.franchise')
@section('title', 'Class Practice')
@section('page-title', 'Class Practice')

@section('content')

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-border shadow-sm p-8 sm:p-10">

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('franchise.class-practice.store') }}" class="space-y-8"
              x-data="{
                  time: '{{ old('time_per_question_seconds', '2') }}',
                  length: '{{ old('session_length_minutes', '8') }}'
              }">
            @csrf

            {{-- Select Level --}}
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Select Level</label>
                <div class="relative">
                    <select name="level_id" required
                            class="w-full appearance-none border border-border rounded-xl px-4 py-3.5 text-base text-gray-700 bg-bg-light focus:outline-none focus:ring-2 focus:ring-fran focus:bg-white">
                        <option value="">Choose a level…</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>
                                @if($level->title){{ $level->title }} (L{{ $level->number }})@else Level {{ $level->number }}@endif
                            </option>
                        @endforeach
                    </select>
                    <svg class="w-5 h-5 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Time per steps --}}
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Time per steps</label>
                <div class="grid grid-cols-3 gap-4">
                    @foreach(['2' => '2 Seconds', '2.5' => '2.5 Seconds', '3' => '3 Seconds'] as $val => $lbl)
                        <label class="cursor-pointer">
                            <input type="radio" name="time_per_question_seconds" value="{{ $val }}" x-model="time" class="sr-only">
                            <span class="block text-center py-4 rounded-xl border text-base font-medium transition-colors"
                                  :class="time === '{{ $val }}'
                                      ? 'bg-blue-50 border-fran text-fran font-semibold'
                                      : 'bg-white border-border text-gray-700 hover:border-fran'">{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Session Length --}}
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Session Length</label>
                <div class="grid grid-cols-2 gap-4">
                    @foreach(['8' => '8 min', '10' => '10 min'] as $val => $lbl)
                        <label class="cursor-pointer">
                            <input type="radio" name="session_length_minutes" value="{{ $val }}" x-model="length" class="sr-only">
                            <span class="block text-center py-4 rounded-xl border text-base font-medium transition-colors"
                                  :class="length === '{{ $val }}'
                                      ? 'bg-blue-50 border-fran text-fran font-semibold'
                                      : 'bg-white border-border text-gray-700 hover:border-fran'">{{ $lbl }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Number of Questions --}}
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Number of Questions</label>
                <div class="relative">
                    <select name="total_questions" required
                            class="w-full appearance-none border border-border rounded-xl px-4 py-3.5 text-base text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-fran">
                        @foreach(['100', '120', '150'] as $opt)
                            <option value="{{ $opt }}" @selected(old('total_questions', '150') == $opt)>{{ $opt }} Questions</option>
                        @endforeach
                    </select>
                    <svg class="w-5 h-5 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <hr class="border-border">

            {{-- Audio Dictation toggle --}}
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-semibold text-gray-800">Audio Dictation</p>
                    <p class="text-sm text-gray-500 mt-0.5">Play voice for each number automatically</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="audio_dictation" value="0">
                    <input type="checkbox" name="audio_dictation" value="1" class="sr-only peer"
                           {{ old('audio_dictation', '1') ? 'checked' : '' }}>
                    <div class="w-12 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-6 peer-checked:bg-fran after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
            </div>

            <hr class="border-border">

            <button type="submit"
                    class="w-full py-4 bg-fran text-white rounded-2xl text-base font-semibold hover:bg-fran-dark transition-colors">
                Next Question
            </button>
        </form>
    </div>
</div>

@endsection
