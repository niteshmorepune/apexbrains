@extends('layouts.external')
@section('title', 'Practice — Q' . ($index + 1))

@section('content')
@php
    $diffLabel = ucfirst($session->difficulty ?? 'Mixed');
    $elapsedSeconds = (int) abs($session->created_at->diffInSeconds(now()));
@endphp

<div x-data="{
        selected: null,
        elapsed: {{ $elapsedSeconds }},
        questionText: @js($question['question_text']),
        tick() {
            this.elapsed++;
            setTimeout(() => this.tick(), 1000);
        },
        speak() { if (window.ApexSpeak) window.ApexSpeak.speak(this.questionText); },
        get clock() { const m = Math.floor(this.elapsed/60), s = this.elapsed%60; return m+':'+(s<10?'0':'')+s; }
     }" x-init="tick(); $nextTick(() => speak())">

    {{-- Header --}}
    <div class="px-4 pt-5 pb-2 flex items-center gap-2">
        <form method="POST" action="{{ route('external.practice.submit', $session) }}" id="exitForm">
            @csrf
            <button type="submit" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
        </form>
        <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900">{{ $diffLabel }} Practice</h1>
    </div>

    {{-- Timer pills --}}
    <div class="px-4 flex items-center justify-between">
        <span class="bg-fran text-white text-xs font-bold px-3 py-1.5 rounded-full">Q{{ $index + 1 }} of {{ $totalCount }}</span>
        <span class="bg-gray-500 text-white text-xs font-bold px-3 py-1.5 rounded-full" x-text="clock"></span>
    </div>

    {{-- Calculate prompt --}}
    <div class="px-4 mt-5 flex items-center justify-between">
        <p class="text-sm text-gray-500">Calculate mentally :</p>
        <button type="button" @click="speak()" aria-label="Play audio"
                class="w-9 h-9 -mr-1 rounded-full bg-fran-light text-fran flex items-center justify-center text-lg active:scale-95">🔊</button>
    </div>

    {{-- Big number display --}}
    <div class="px-4 mt-3">
        <div class="bg-white rounded-2xl border border-border py-10 px-4 text-center min-h-[180px] flex items-center justify-center">
            <x-sum-vertical :text="$question['question_text']" :size="40" />
        </div>
    </div>

    {{-- Answer options --}}
    <form method="POST" action="{{ route('external.practice.answer', $session) }}" id="answerForm" class="px-4 mt-5">
        @csrf
        <p class="text-sm text-gray-500 mb-2">Select your answer :</p>
        <div class="grid grid-cols-2 gap-3">
            @foreach(['a' => $question['option_a'], 'b' => $question['option_b'], 'c' => $question['option_c'], 'd' => $question['option_d']] as $letter => $option)
                @if($option !== null && $option !== '')
                    <label class="cursor-pointer">
                        <input type="radio" name="answer" value="{{ $letter }}" class="sr-only peer"
                               x-model="selected" @change="$nextTick(() => document.getElementById('answerForm').submit())">
                        <div class="flex items-center gap-3 bg-white border-2 border-border rounded-2xl px-4 py-4 peer-checked:border-fran peer-checked:bg-fran-light">
                            <span class="w-7 h-7 rounded-full bg-bg-mid text-gray-500 flex items-center justify-center text-xs font-bold flex-shrink-0 peer-checked:bg-fran peer-checked:text-white">{{ strtoupper($letter) }}</span>
                            <span class="text-base font-bold text-gray-800">{{ $option }}</span>
                        </div>
                    </label>
                @endif
            @endforeach
        </div>
    </form>

    <p class="text-center text-xs text-gray-400 mt-4">Tap an option to continue</p>

</div>

@push('scripts')
@include('partials.speak-script')
@endpush
@endsection
