<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} — Apex Brains</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg-light font-sans min-h-screen"
      x-data="examEngine()"
      x-init="init()"
      @visibilitychange.document="onVisibilityChange($event)"
      @fullscreenchange.document="onFullscreenChange()">

    {{-- Tab-switch warning overlay --}}
    <div x-show="tabSwitchWarning"
         class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-6"
         x-cloak>
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center shadow-2xl">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p class="font-bold text-gray-800 mb-1">Tab Switch Detected!</p>
            <p class="text-sm text-gray-500 mb-4">You switched away from this exam. This has been recorded.</p>
            <p class="text-xs text-red-500 font-medium mb-4" x-text="`Total violations: ${tabSwitches}`"></p>
            <button @click="tabSwitchWarning = false; requestFullscreen()"
                    class="w-full py-2.5 bg-fran text-white rounded-xl text-sm font-semibold">
                Return to Exam
            </button>
        </div>
    </div>

    {{-- Time expired overlay --}}
    <div x-show="timeUp"
         class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-6"
         x-cloak>
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center">
            <p class="font-bold text-gray-800 mb-2">Time's Up!</p>
            <p class="text-sm text-gray-500 mb-4">Your exam is being submitted automatically.</p>
            <div class="w-6 h-6 border-2 border-fran border-t-transparent rounded-full animate-spin mx-auto"></div>
        </div>
    </div>

    {{-- Header --}}
    <div class="bg-fran text-white px-4 py-3 flex items-center justify-between sticky top-0 z-20">
        <div>
            <p class="font-semibold text-sm">{{ $exam->title }}</p>
            <p class="text-white/70 text-xs" x-text="`Q${currentIndex + 1} of ${questions.length}`"></p>
        </div>
        <div class="flex items-center gap-4">
            <div x-show="tabSwitches > 0" class="text-xs bg-red-500 text-white px-2 py-1 rounded-lg">
                <span x-text="`${tabSwitches} violation${tabSwitches > 1 ? 's' : ''}`"></span>
            </div>
            <div class="text-center">
                <p class="text-xl font-black tabular-nums" :class="timeLeft <= 60 ? 'text-red-300' : 'text-white'" x-text="formatTime(timeLeft)"></p>
                <p class="text-white/60 text-xs">remaining</p>
            </div>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="h-1 bg-white/20">
        <div class="h-full bg-white transition-all duration-300"
             :style="`width: ${((currentIndex) / questions.length) * 100}%`"></div>
    </div>

    {{-- Question area --}}
    <div class="max-w-lg mx-auto px-4 py-6 space-y-4">

        <template x-if="questions.length > 0">
            <div>
                <div class="bg-white rounded-2xl border border-border p-5 mb-4">
                    <p class="text-sm font-bold text-gray-800 leading-relaxed"
                       x-text="questions[currentIndex]?.question_text"></p>
                </div>

                <div class="space-y-3">
                    <template x-for="opt in ['a','b','c','d']" :key="opt">
                        <template x-if="questions[currentIndex]?.['option_' + opt]">
                            <button @click="selectAnswer(opt)"
                                    :class="{
                                        'border-fran bg-fran/5': answers[questions[currentIndex]?.id] === opt,
                                        'border-border bg-white': answers[questions[currentIndex]?.id] !== opt
                                    }"
                                    class="w-full flex items-center gap-3 rounded-2xl border-2 px-4 py-3.5 text-left transition-colors">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 transition-colors"
                                      :class="{
                                          'bg-fran text-white': answers[questions[currentIndex]?.id] === opt,
                                          'bg-bg-mid text-gray-500': answers[questions[currentIndex]?.id] !== opt
                                      }"
                                      x-text="opt.toUpperCase()"></span>
                                <span class="text-sm text-gray-700" x-text="questions[currentIndex]?.['option_' + opt]"></span>
                            </button>
                        </template>
                    </template>
                </div>

                {{-- Navigation --}}
                <div class="flex items-center justify-between mt-5">
                    <button @click="prevQuestion()"
                            x-show="currentIndex > 0"
                            class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light">
                        ← Previous
                    </button>
                    <div x-show="currentIndex === 0"></div>

                    <template x-if="currentIndex < questions.length - 1">
                        <button @click="nextQuestion()"
                                class="px-5 py-2 bg-fran text-white rounded-xl text-sm font-semibold">
                            Next →
                        </button>
                    </template>
                    <template x-if="currentIndex === questions.length - 1">
                        <button @click="confirmSubmit()"
                                class="px-5 py-2 bg-green-600 text-white rounded-xl text-sm font-semibold">
                            Submit Exam
                        </button>
                    </template>
                </div>

                {{-- Question dots --}}
                <div class="flex flex-wrap gap-1.5 mt-4 justify-center">
                    <template x-for="(q, i) in questions" :key="i">
                        <button @click="currentIndex = i"
                                class="w-7 h-7 rounded-lg text-xs font-bold transition-colors"
                                :class="{
                                    'bg-fran text-white': i === currentIndex,
                                    'bg-green-500 text-white': answers[q.id] && i !== currentIndex,
                                    'bg-bg-mid text-gray-500': !answers[q.id] && i !== currentIndex
                                }"
                                x-text="i + 1">
                        </button>
                    </template>
                </div>

                <p class="text-center text-xs text-gray-400 mt-2">
                    <span x-text="Object.keys(answers).length"></span> of <span x-text="questions.length"></span> answered
                </p>
            </div>
        </template>

    </div>

    {{-- Submit form (hidden) --}}
    <form id="submitForm" method="POST" action="{{ route('student.exams.submit', $exam) }}" class="hidden">
        @csrf
        <input type="hidden" name="tab_switches" id="tabSwitchInput">
    </form>

</body>

<script>
function examEngine() {
    return {
        questions: @json($questions),
        answers: @json($savedAnswers),
        currentIndex: 0,
        timeLeft: {{ $remaining }},
        tabSwitches: {{ $attempt->tab_switch_count ?? 0 }},
        tabSwitchWarning: false,
        timeUp: false,
        saveQueue: {},

        init() {
            this.startTimer();
            this.requestFullscreen();
        },

        startTimer() {
            const tick = setInterval(() => {
                if (this.timeLeft > 0) {
                    this.timeLeft--;
                } else {
                    clearInterval(tick);
                    this.timeUp = true;
                    setTimeout(() => this.doSubmit(), 2000);
                }
            }, 1000);
        },

        formatTime(secs) {
            const m = Math.floor(secs / 60).toString().padStart(2, '0');
            const s = (secs % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        },

        requestFullscreen() {
            const el = document.documentElement;
            if (el.requestFullscreen) el.requestFullscreen().catch(() => {});
        },

        onVisibilityChange() {
            if (document.visibilityState === 'hidden' && !this.timeUp) {
                this.tabSwitches++;
                this.tabSwitchWarning = true;
                this.saveTabSwitches();
            }
        },

        onFullscreenChange() {
            if (!document.fullscreenElement && !this.timeUp) {
                this.tabSwitchWarning = true;
            }
        },

        saveTabSwitches() {
            const currentQ = this.questions[this.currentIndex];
            if (!currentQ) return;
            fetch('{{ route('student.exams.answer', $exam) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    question_id: currentQ.id,
                    selected_answer: this.answers[currentQ.id] ?? 'a',
                    tab_switches: this.tabSwitches,
                }),
            });
        },

        selectAnswer(opt) {
            const q = this.questions[this.currentIndex];
            if (!q) return;
            this.answers[q.id] = opt;
            this.saveAnswer(q.id, opt);
        },

        saveAnswer(questionId, answer) {
            fetch('{{ route('student.exams.answer', $exam) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    question_id: questionId,
                    selected_answer: answer,
                    tab_switches: this.tabSwitches,
                }),
            });
        },

        nextQuestion() {
            if (this.currentIndex < this.questions.length - 1) this.currentIndex++;
        },

        prevQuestion() {
            if (this.currentIndex > 0) this.currentIndex--;
        },

        confirmSubmit() {
            const unanswered = this.questions.length - Object.keys(this.answers).length;
            if (unanswered > 0) {
                if (!confirm(`You have ${unanswered} unanswered question${unanswered > 1 ? 's' : ''}. Submit anyway?`)) return;
            }
            this.doSubmit();
        },

        doSubmit() {
            document.getElementById('tabSwitchInput').value = this.tabSwitches;
            document.getElementById('submitForm').submit();
        },
    };
}
</script>
</html>
