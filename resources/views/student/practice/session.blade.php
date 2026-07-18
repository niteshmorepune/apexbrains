@extends('layouts.student')
@section('title', 'Practice — Q' . ($index + 1))

@section('content')
@php
    $isAnzan = isset($question['question_type']) && in_array($question['question_type'], ['audio', 'anzan']);
    $diffLabel = ucfirst($session->difficulty ?? 'Practice');
    $elapsedSeconds = (int) abs($session->created_at->diffInSeconds(now()));
@endphp

<div x-data="{
        selected: null,
        elapsed: {{ $elapsedSeconds }},
        questionText: @js($question['question_text']),
        flashSpeed: {{ (float) ($session->flash_speed_seconds ?? 2) }},
        terms: [],
        termIndex: 0,
        display: '',
        finished: false,
        flashTimer: null,
        tick() {
            this.elapsed++;
            setTimeout(() => this.tick(), 1000);
        },
        speak() { if (window.ApexSpeak) window.ApexSpeak.speak(this.questionText); },
        get clock() { const m = Math.floor(this.elapsed/60), s = this.elapsed%60; return m+':'+(s<10?'0':'')+s; },

        // 1 Digit Popup — flash each term of the sum on its own, one at a
        // time, instead of showing the whole vertical sum at once.
        parseTerms(text) {
            if (!text) return [];
            const cleaned = String(text).replace(/=\s*\?|\?/g, '');
            const opMap = { '*': '×', 'x': '×', 'X': '×', '/': '÷', '-': '−', '–': '−' };
            const re = /([+\-−–×xX*÷/])?\s*(\d+(?:\.\d+)?)/g;
            const out = []; let m, first = true;
            while ((m = re.exec(cleaned)) !== null) {
                let op = m[1] || '';
                if (op && opMap[op]) op = opMap[op];
                out.push(first && !op ? m[2] : (op || '+') + m[2]);
                first = false;
            }
            return out.length ? out : [String(text).trim()];
        },
        numericPart(term) { return String(term).replace(/^[+\-−–×xX*÷/]\s*/, '').trim(); },
        startFlash() {
            clearTimeout(this.flashTimer);
            this.terms = this.parseTerms(this.questionText);
            this.termIndex = 0;
            this.finished = false;
            this.showTerm();
        },
        showTerm() {
            clearTimeout(this.flashTimer);
            if (this.termIndex >= this.terms.length) {
                this.finished = true;
                this.display = '= ?';
                return;
            }
            this.display = this.numericPart(this.terms[this.termIndex]);
            const flashMs = Math.max(400, this.flashSpeed * 1000);
            const gapMs = Math.min(260, flashMs * 0.18);
            this.flashTimer = setTimeout(() => {
                this.display = '';
                this.flashTimer = setTimeout(() => {
                    this.termIndex++;
                    this.showTerm();
                }, gapMs);
            }, flashMs - gapMs);
        },
     }" x-init="tick(); $nextTick(() => speak()); startFlash()">

    {{-- Header --}}
    <div class="px-4 pt-5 pb-2 flex items-center gap-2">
        <form method="POST" action="{{ route('student.practice.submit', $session) }}" id="exitForm">
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

    {{-- Warning --}}
    <div class="px-4 mt-2">
        <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-center">
            <p class="text-[11px] text-amber-700 font-medium">Warning — Do not switch tabs — session will be flagged</p>
        </div>
    </div>

    {{-- Question number strip (auto-scrolls to keep the current question centered) --}}
    <div id="qStrip" class="relative px-4 mt-3 overflow-x-auto">
        <div class="flex gap-2 w-max">
            @for($i = 0; $i < $totalCount; $i++)
                @php $isDone = isset($answered[$i]); $isCur = $i === $index; @endphp
                <span @if($isCur) id="qCurrent" @endif
                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                    {{ $isCur ? 'bg-fran text-white' : ($isDone ? 'bg-stu-light text-stu' : 'bg-white border border-border text-gray-400') }}">
                    {{ $i + 1 }}
                </span>
            @endfor
        </div>
    </div>

    {{-- Calculate prompt --}}
    <div class="px-4 mt-5 flex items-center justify-between">
        <p class="text-sm text-gray-500">Calculate mentally :</p>
        <button type="button" @click="speak(); startFlash()" aria-label="Replay"
                class="w-9 h-9 -mr-1 rounded-full bg-stu-light text-stu flex items-center justify-center text-lg active:scale-95">🔊</button>
    </div>

    {{-- Popup display — one number at a time --}}
    <div class="px-4 mt-3">
        <div class="bg-white rounded-2xl border border-border py-10 px-4 text-center min-h-[180px] flex items-center justify-center">
            <p class="font-mono font-black text-gray-900 tabular-nums" style="font-size: 56px;" x-text="display"></p>
        </div>
    </div>

    {{-- Answer options --}}
    <form method="POST" action="{{ route('student.practice.answer', $session) }}" id="answerForm" class="px-4 mt-5">
        @csrf
        <p class="text-sm text-gray-500 mb-2">Select your answer :</p>
        <div class="grid grid-cols-2 gap-3">
            @foreach(['a' => $question['option_a'], 'b' => $question['option_b'], 'c' => $question['option_c'], 'd' => $question['option_d']] as $letter => $option)
                @if($option !== null && $option !== '')
                    <label class="cursor-pointer">
                        <input type="radio" name="answer" value="{{ $letter }}" class="sr-only peer"
                               x-model="selected" @change="$nextTick(() => document.getElementById('answerForm').submit())">
                        <div class="flex items-center gap-3 bg-white border-2 border-border rounded-2xl px-4 py-4 peer-checked:border-stu peer-checked:bg-stu-light">
                            <span class="w-7 h-7 rounded-full bg-bg-mid text-gray-500 flex items-center justify-center text-xs font-bold flex-shrink-0 peer-checked:bg-stu peer-checked:text-white">{{ strtoupper($letter) }}</span>
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
<script>
    // Keep the active question pill centered in the horizontally-scrolling strip.
    (function () {
        var strip = document.getElementById('qStrip');
        var cur   = document.getElementById('qCurrent');
        if (strip && cur) {
            strip.scrollLeft = cur.offsetLeft - (strip.clientWidth / 2) + (cur.offsetWidth / 2);
        }
    })();
</script>
@endpush
@endsection
