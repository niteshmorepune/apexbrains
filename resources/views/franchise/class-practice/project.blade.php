<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $session->title }} — Projector</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col" x-data="projector()" x-init="init()">

    {{-- Top bar --}}
    <div class="flex items-center justify-between px-8 py-4 border-b border-white/10">
        <div class="flex items-center gap-4">
            <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white font-black text-sm">AB</div>
            <div>
                <p class="text-white font-semibold text-sm">{{ $session->title }}</p>
                <p class="text-white/50 text-xs">Level {{ $session->level?->number }} · Code: <span class="font-mono font-bold text-white">{{ $session->session_code }}</span></p>
            </div>
        </div>
        <div class="flex items-center gap-3 text-sm text-white/50">
            <span x-text="state.current_index > 0 ? `Q${state.current_index} of ${state.total}` : 'Ready'"></span>
            <span class="w-px h-4 bg-white/20"></span>
            <span x-text="`${state.time_per_question}s timer`"></span>
        </div>
    </div>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col items-center justify-center px-12 py-8">

        {{-- Pending / not started --}}
        <template x-if="state.status === 'pending'">
            <div class="text-center">
                <p class="text-white/40 text-lg mb-2">Session ready</p>
                <p class="text-white text-4xl font-black mb-8">Press Start to begin</p>
                <form method="POST" action="{{ route('franchise.class-practice.next', $session) }}">
                    @csrf
                    <button type="submit"
                            class="px-10 py-4 bg-fran text-white rounded-2xl text-xl font-bold hover:bg-blue-700 transition-colors">
                        Start Session
                    </button>
                </form>
            </div>
        </template>

        {{-- Active --}}
        <template x-if="state.status === 'active'">
            <div class="w-full max-w-4xl">
                {{-- Question number + timer --}}
                <div class="flex items-center justify-between mb-6">
                    <span class="text-white/50 text-lg" x-text="`Question ${state.current_index} of ${state.total}`"></span>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black tabular-nums"
                              :class="timeLeft <= 5 ? 'text-red-400' : 'text-white'"
                              x-text="timeLeft"></span>
                        <span class="text-white/40 text-sm">sec</span>
                    </div>
                </div>

                {{-- Timer bar --}}
                <div class="w-full h-1.5 bg-white/10 rounded-full mb-10 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-1000"
                         :class="timeLeft <= 5 ? 'bg-red-500' : 'bg-fran'"
                         :style="`width: ${(timeLeft / state.time_per_question) * 100}%`"></div>
                </div>

                {{-- Question text --}}
                <template x-if="state.question">
                    <div>
                        <p class="text-white text-3xl font-bold leading-snug mb-10 text-center"
                           x-text="state.question.text"></p>

                        {{-- Options --}}
                        <div class="grid grid-cols-2 gap-4">
                            <template x-for="(label, key) in {A: 'option_a', B: 'option_b', C: 'option_c', D: 'option_d'}" :key="key">
                                <div x-show="state.question[key]"
                                     :class="{
                                         'border-green-400 bg-green-400/10': state.revealed && state.question.correct_answer === label,
                                         'border-white/20 bg-white/5': !state.revealed || state.question.correct_answer !== label
                                     }"
                                     class="rounded-2xl border-2 p-5 transition-colors">
                                    <p class="text-white/50 text-sm font-bold mb-1" x-text="label"></p>
                                    <p class="text-white text-xl font-semibold" x-text="state.question[key]"></p>
                                    <template x-if="state.revealed && state.question.correct_answer === label">
                                        <p class="text-green-400 text-sm font-bold mt-1">✓ Correct Answer</p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="!state.question">
                    <p class="text-white/30 text-xl text-center">Loading question...</p>
                </template>
            </div>
        </template>

        {{-- Ended --}}
        <template x-if="state.status === 'ended'">
            <div class="text-center">
                <p class="text-white text-4xl font-black mb-4">Session Complete!</p>
                <p class="text-white/50 text-lg mb-8" x-text="`${state.current_index} of ${state.total} questions covered`"></p>
                <a href="{{ route('franchise.class-practice.results', $session) }}"
                   class="px-8 py-3 bg-fran text-white rounded-xl font-semibold hover:bg-blue-700">
                    View Results
                </a>
            </div>
        </template>

    </div>

    {{-- Teacher controls (bottom bar) --}}
    <div class="border-t border-white/10 px-8 py-4 flex items-center justify-between">
        <a href="{{ route('franchise.class-practice.show', $session) }}"
           class="text-sm text-white/40 hover:text-white/70">← Back to session</a>

        <div class="flex items-center gap-3" x-show="state.status === 'active'">
            {{-- Reveal --}}
            <form method="POST" action="{{ route('franchise.class-practice.reveal', $session) }}" x-show="!state.revealed">
                @csrf
                <button type="submit"
                        class="px-5 py-2 border border-white/30 text-white rounded-xl text-sm font-medium hover:bg-white/10">
                    Reveal Answer
                </button>
            </form>

            {{-- Next --}}
            <form method="POST" action="{{ route('franchise.class-practice.next', $session) }}">
                @csrf
                <button type="submit"
                        class="px-6 py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-blue-700">
                    Next Question →
                </button>
            </form>

            {{-- End --}}
            <form method="POST" action="{{ route('franchise.class-practice.end', $session) }}"
                  onsubmit="return confirm('End this session now?')">
                @csrf
                <button type="submit"
                        class="px-5 py-2 border border-red-400/50 text-red-400 rounded-xl text-sm font-medium hover:bg-red-400/10">
                    End Session
                </button>
            </form>
        </div>

        <div x-show="state.status !== 'active'" class="text-sm text-white/30">
            <span x-text="state.status === 'pending' ? 'Not started' : 'Session ended'"></span>
        </div>
    </div>

</body>

<script>
function projector() {
    return {
        state: {
            status: '{{ $session->status }}',
            current_index: {{ $session->current_question_index }},
            total: {{ $session->total_questions }},
            time_per_question: {{ $session->time_per_question_seconds }},
            revealed: false,
            question: null,
        },
        timeLeft: {{ $session->time_per_question_seconds }},
        pollInterval: null,
        timerInterval: null,

        init() {
            this.fetchState();
            this.pollInterval = setInterval(() => this.fetchState(), 3000);
        },

        fetchState() {
            fetch('{{ route('franchise.class-practice.state', $session) }}')
                .then(r => r.json())
                .then(data => {
                    const indexChanged = data.current_index !== this.state.current_index;
                    this.state = data;

                    if (indexChanged || !this.timerInterval) {
                        this.resetTimer();
                    }
                });
        },

        resetTimer() {
            clearInterval(this.timerInterval);
            this.timeLeft = this.state.time_per_question;

            if (this.state.status === 'active') {
                this.timerInterval = setInterval(() => {
                    if (this.timeLeft > 0) this.timeLeft--;
                }, 1000);
            }
        },
    };
}
</script>
</html>
