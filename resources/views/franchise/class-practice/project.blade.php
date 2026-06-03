@extends('layouts.franchise')
@section('title', 'Class Practice')

@section('content')

<div x-data="practicePlayer()" x-init="init()">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-[13px] text-gray-400 mb-1">
        <a href="{{ route('franchise.class-practice.index') }}" class="hover:text-gray-600">Franchises</a>
        <span>/</span>
        <span class="font-semibold text-gray-700">Class Practice</span>
    </nav>

    {{-- Page title --}}
    <h1 class="text-[26px] font-extrabold text-gray-900 mb-5">Class Practice</h1>

    {{-- Player card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-border overflow-hidden max-w-[1140px]">

        {{-- Card header --}}
        <div class="relative flex items-center justify-center px-6 py-4 border-b border-border">
            <p class="text-lg font-semibold text-gray-900"
               x-text="state.status === 'active' ? `Question ${state.index} of ${state.total}` : 'Ready to Begin'"></p>

            <div class="absolute right-5 flex items-center gap-4" x-show="state.status === 'active'">
                {{-- Audio --}}
                <button type="button" @click="playAudio()" title="Play audio"
                        class="text-gray-700 hover:text-fran transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5L6 9H2v6h4l5 4V5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.54 8.46a5 5 0 010 7.07M19.07 4.93a10 10 0 010 14.14"/>
                    </svg>
                </button>
                {{-- Pause / Play toggle --}}
                <button type="button" @click="togglePause()" :title="paused ? 'Resume' : 'Pause'"
                        class="text-gray-700 hover:text-fran transition-colors">
                    <svg x-show="!paused" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="6" y="5" width="4" height="14" rx="1"/>
                        <rect x="14" y="5" width="4" height="14" rx="1"/>
                    </svg>
                    <svg x-show="paused" x-cloak class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 5.5v13a1 1 0 001.54.84l10-6.5a1 1 0 000-1.68l-10-6.5A1 1 0 007 5.5z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Card body --}}
        <div class="bg-bg-light px-8 pt-14 pb-10 flex flex-col items-center min-h-[460px]">

            {{-- Active: flashcard (terms flashed one at a time) --}}
            <template x-if="state.status === 'active'">
                <div class="flex-1 w-full grid place-items-center">
                    {{-- Flashing term --}}
                    <p x-show="!finished"
                       class="font-black text-gray-900 leading-none text-center break-words max-w-full select-none"
                       :style="`font-size: clamp(64px, ${flashSize}vw, 200px)`"
                       x-text="display"></p>

                    {{-- Well done: shown once all terms of the question have flashed --}}
                    <div x-show="finished" x-cloak x-transition class="text-center">
                        <span class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-100 text-green-700 rounded-full text-lg font-bold mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.5 2.5 4.5-5"/>
                            </svg>
                            Well Done
                        </span>
                        <p class="text-gray-500 text-base mb-2">You've completed question <span class="font-semibold text-gray-700" x-text="state.index"></span> of <span x-text="state.total"></span></p>
                        <p class="text-7xl sm:text-8xl font-black text-fran" x-text="`${state.index}/${state.total}`"></p>
                        <p class="text-gray-400 text-sm mt-4">Tap <span class="font-semibold text-gray-600">Next Question</span> to continue</p>
                    </div>
                </div>
            </template>

            {{-- Pending: start prompt --}}
            <template x-if="state.status === 'pending'">
                <div class="flex-1 w-full grid place-items-center text-center">
                    <div>
                        <p class="text-gray-400 text-lg mb-6">Session ready · {{ $session->total_questions }} questions</p>
                        <form method="POST" action="{{ route('franchise.class-practice.next', $session) }}">
                            @csrf
                            <button type="submit"
                                    class="px-10 py-4 bg-fran text-white rounded-2xl text-xl font-bold hover:bg-fran-dark transition-colors">
                                Start Practice
                            </button>
                        </form>
                    </div>
                </div>
            </template>

            {{-- Progress bar --}}
            <div class="w-full max-w-[420px]" x-show="state.status === 'active'">
                <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-fran rounded-full transition-all duration-200 ease-linear"
                         :style="`width: ${progressPct}%`"></div>
                </div>
            </div>
        </div>

        {{-- Card footer --}}
        <div class="bg-white border-t border-border px-6 py-4 flex items-center justify-between gap-4">
            {{-- End Practice --}}
            <form method="POST" action="{{ route('franchise.class-practice.end', $session) }}"
                  onsubmit="return confirm('End this practice session now?')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 text-red-600 font-semibold hover:text-red-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5m0 0l-5-5m5 5H9M13 5v-.5a2.5 2.5 0 00-2.5-2.5h-4A2.5 2.5 0 004 4.5v15A2.5 2.5 0 006.5 22h4a2.5 2.5 0 002.5-2.5V19"/>
                    </svg>
                    End Practice
                </button>
            </form>

            <div class="flex items-center gap-3" x-show="state.status === 'active'">
                {{-- Restart Question --}}
                <button type="button" @click="restart()"
                        class="flex items-center gap-2 px-5 py-2.5 border border-border rounded-full text-sm font-semibold text-gray-700 hover:bg-bg-light transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 2v6h6M3.51 9a9 9 0 102.13-3.36L3 8"/>
                    </svg>
                    Restart Question
                </button>

                {{-- Next Question --}}
                <form method="POST" action="{{ route('franchise.class-practice.next', $session) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 bg-fran text-white rounded-full text-sm font-semibold hover:bg-fran-dark transition-colors">
                        Next Question
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m0 0l-6-6m6 6l-6 6"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function practicePlayer() {
    return {
        state: {
            status: @json($session->status),
            index: {{ $session->current_question_index }},
            total: {{ $session->total_questions }},
            duration: {{ (float) $session->time_per_question_seconds }},
            question: @json($currentQuestion?->question_text),
        },
        audioPath: @json($currentQuestion?->audio_file_path ? asset('storage/' . $currentQuestion->audio_file_path) : null),
        audioDictation: {{ $session->audio_dictation ? 'true' : 'false' }},

        terms: [],         // signed flash terms, e.g. ['9', '+8']
        termIndex: 0,      // which term is currently showing
        display: '',       // text on the flashcard right now ('' during the blank gap)
        finished: false,   // whole sequence has been flashed
        paused: false,
        timer: null,
        flashSize: 18,

        init() {
            this.terms = this.parseTerms(this.state.question);
            if (this.state.status === 'active') {
                // user gesture (page nav) lets audio autoplay in most browsers
                this.startSequence();
            }
        },

        // Break an arithmetic expression into abacus-style signed terms.
        // "9 + 8 = ?"        -> ['9', '+8']
        // "(7 × 13) − 18 = ?" -> ['7', '×13', '−18']
        parseTerms(text) {
            if (!text) return [];
            const cleaned = String(text).replace(/=\s*\?/g, '').replace(/\?/g, '');
            const opMap = { '*': '×', 'x': '×', 'X': '×', '/': '÷', '-': '−', '–': '−' };
            const re = /([+\-−–×x*÷/])?\s*(\d+(?:\.\d+)?)/g;
            const out = [];
            let m, first = true;
            while ((m = re.exec(cleaned)) !== null) {
                let op = m[1] || '';
                if (op && opMap[op]) op = opMap[op];
                out.push(first && !op ? m[2] : (op || '+') + m[2]);
                first = false;
            }
            return out.length ? out : [String(text).trim()];
        },

        get progressPct() {
            if (!this.terms.length) return 0;
            const done = this.finished ? this.terms.length : this.termIndex;
            return Math.min(100, (done / this.terms.length) * 100);
        },

        startSequence() {
            clearTimeout(this.timer);
            this.termIndex = 0;
            this.finished = false;
            this.paused = false;
            this.showTerm();
        },

        showTerm() {
            clearTimeout(this.timer);
            if (this.paused) return;

            if (this.termIndex >= this.terms.length) {
                this.finished = true;
                this.display = '= ?';
                return;
            }

            this.display = this.terms[this.termIndex];
            this.speak(this.display);

            const flashMs = Math.max(400, this.state.duration * 1000);
            const gapMs = Math.min(260, flashMs * 0.18); // brief blank so repeats are distinct

            this.timer = setTimeout(() => {
                if (this.paused) return;
                this.display = ''; // blank gap
                this.timer = setTimeout(() => {
                    if (this.paused) return;
                    this.termIndex++;
                    this.showTerm();
                }, gapMs);
            }, flashMs - gapMs);
        },

        togglePause() {
            this.paused = !this.paused;
            if (this.paused) {
                clearTimeout(this.timer);
                window.speechSynthesis?.cancel();
            } else if (!this.finished) {
                this.showTerm(); // resume current term
            }
        },

        restart() {
            window.speechSynthesis?.cancel();
            this.startSequence();
        },

        // Speaker button: replay the recorded dictation, or re-run the flash sequence.
        playAudio() {
            if (this.audioPath) {
                const a = new Audio(this.audioPath);
                a.play().catch(() => {});
                return;
            }
            this.restart();
        },

        // Speak a single term as it flashes (TTS fallback when no recorded file).
        speak(term) {
            if (this.audioPath || !this.audioDictation || !term) return;
            if (!window.speechSynthesis) return;
            window.speechSynthesis.cancel();
            const u = new SpeechSynthesisUtterance(this.spokenForm(term));
            u.rate = 0.9;
            window.speechSynthesis.speak(u);
        },

        spokenForm(term) {
            return term
                .replace('×', ' times ')
                .replace('÷', ' divided by ')
                .replace('+', 'plus ')
                .replace('−', 'minus ');
        },
    };
}
</script>
@endpush

@endsection
