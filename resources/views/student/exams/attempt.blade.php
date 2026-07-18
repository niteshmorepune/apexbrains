<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} — Apex Brains</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{font-family:'Inter',ui-sans-serif,system-ui,sans-serif}[x-cloak]{display:none!important}</style>
</head>
<body class="bg-stu-bg min-h-screen"
      x-data="examEngine()"
      x-init="init()"
      @visibilitychange.document="onVisibilityChange($event)"
      @fullscreenchange.document="onFullscreenChange()">

<div class="max-w-md mx-auto min-h-screen">

    {{-- Tab-switch warning overlay --}}
    <div x-show="tabSwitchWarning" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-6" x-cloak>
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center shadow-2xl">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">⚠️</div>
            <p class="font-bold text-gray-800 mb-1">Tab Switch Detected!</p>
            <p class="text-sm text-gray-500 mb-4">You switched away from this exam. This has been recorded.</p>
            <p class="text-xs text-red-500 font-medium mb-4" x-text="`Total violations: ${tabSwitches}`"></p>
            <button @click="tabSwitchWarning = false; requestFullscreen()" class="w-full py-2.5 bg-fran text-white rounded-xl text-sm font-semibold">Return to Exam</button>
        </div>
    </div>

    {{-- Time expired overlay --}}
    <div x-show="timeUp" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-6" x-cloak>
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center">
            <p class="font-bold text-gray-800 mb-2">Time's Up!</p>
            <p class="text-sm text-gray-500 mb-4">Your exam is being submitted automatically.</p>
            <div class="w-6 h-6 border-2 border-fran border-t-transparent rounded-full animate-spin mx-auto"></div>
        </div>
    </div>

    {{-- Header --}}
    <div class="px-4 pt-5 pb-2 flex items-center gap-2">
        <button @click="confirmSubmit()" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900 truncate">{{ $exam->title }}</h1>
    </div>

    {{-- Timer pills --}}
    <div class="px-4 flex items-center justify-between">
        <span class="bg-fran text-white text-xs font-bold px-3 py-1.5 rounded-full" x-text="`Q${currentIndex + 1} of ${questions.length}`"></span>
        <span class="text-white text-xs font-bold px-3 py-1.5 rounded-full" :class="timeLeft <= 60 ? 'bg-red-600 animate-pulse' : 'bg-red-500'" x-text="formatTime(timeLeft)"></span>
    </div>

    {{-- Warning --}}
    <div class="px-4 mt-2">
        <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-center">
            <p class="text-[11px] text-amber-700 font-medium">Warning — Do not switch tabs — exam will be flagged</p>
        </div>
    </div>

    <template x-if="questions.length > 0">
        <div>
            {{-- Calculate prompt --}}
            <div class="px-4 mt-5 flex items-center justify-between">
                <p class="text-sm text-gray-500">Calculate mentally :</p>
                <button type="button" @click="speak()" aria-label="Play audio"
                        class="w-9 h-9 -mr-1 rounded-full bg-stu-light text-stu flex items-center justify-center text-lg active:scale-95">🔊</button>
            </div>

            {{-- Big number display --}}
            <div class="px-4 mt-3">
                <div class="bg-white rounded-2xl border border-border py-10 px-4 text-center min-h-[170px] flex items-center justify-center">
                    <p class="font-black text-gray-900 leading-tight whitespace-pre-line" style="font-size:42px" x-text="questions[currentIndex]?.question_text"></p>
                </div>
            </div>

            {{-- Answer grid --}}
            <div class="px-4 mt-5">
                <p class="text-sm text-gray-500 mb-2">Select your answer :</p>
                <div class="grid grid-cols-2 gap-3">
                    <template x-for="opt in ['a','b','c','d']" :key="opt">
                        <template x-if="questions[currentIndex]?.['option_' + opt]">
                            <button @click="selectAnswer(opt)"
                                    class="flex items-center gap-3 rounded-2xl border-2 px-4 py-4 text-left"
                                    :class="answers[questions[currentIndex]?.id] === opt ? 'border-stu bg-stu-light' : 'border-border bg-white'">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                      :class="answers[questions[currentIndex]?.id] === opt ? 'bg-stu text-white' : 'bg-bg-mid text-gray-500'"
                                      x-text="opt.toUpperCase()"></span>
                                <span class="text-base font-bold text-gray-800" x-text="questions[currentIndex]?.['option_' + opt]"></span>
                            </button>
                        </template>
                    </template>
                </div>
            </div>

            {{-- Submit appears on the final question — questions advance automatically
                 as they are answered; there is no manual back/next navigation. --}}
            <div class="px-4 mt-6 min-h-[52px]">
                <template x-if="currentIndex === questions.length - 1">
                    <button @click="confirmSubmit()" class="w-full py-3 bg-stu text-white rounded-xl text-sm font-bold">Submit Exam</button>
                </template>
            </div>

            <p class="text-center text-xs text-gray-400 mt-3 pb-6">
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
@include('partials.speak-script')
<script>
function examEngine() {
    return {
        questions: @json($questions),
        answers: @json($savedAnswers),
        currentIndex: 0,
        timeLeft: {{ $remaining }},
        durationSeconds: {{ $durationSeconds }},
        get elapsed() { return this.durationSeconds - this.timeLeft; },
        tabSwitches: {{ $attempt->tab_switch_count ?? 0 }},
        tabSwitchWarning: false,
        timeUp: false,

        init() {
            this.startTimer();
            this.requestFullscreen();
            this.$nextTick(() => this.speak());
            // Read each new question aloud as it appears.
            this.$watch('currentIndex', () => this.speak());
        },

        speak() {
            const q = this.questions[this.currentIndex];
            if (q && window.ApexSpeak) window.ApexSpeak.speak(q.question_text);
        },

        startTimer() {
            const tick = setInterval(() => {
                if (this.timeLeft > 0) { this.timeLeft--; }
                else { clearInterval(tick); this.timeUp = true; setTimeout(() => this.doSubmit(), 2000); }
            }, 1000);
        },

        formatTime(secs) {
            secs = Math.floor(secs);
            const m = Math.floor(secs / 60);
            const s = secs % 60;
            return `${m}:${s.toString().padStart(2, '0')}`;
        },

        requestFullscreen() {
            const el = document.documentElement;
            if (el.requestFullscreen) el.requestFullscreen().catch(() => {});
        },

        onVisibilityChange() {
            if (document.visibilityState === 'hidden' && !this.timeUp) {
                this.tabSwitches++; this.tabSwitchWarning = true; this.saveTabSwitches();
            }
        },

        onFullscreenChange() {
            if (!document.fullscreenElement && !this.timeUp) { this.tabSwitchWarning = true; }
        },

        saveTabSwitches() {
            const currentQ = this.questions[this.currentIndex];
            if (!currentQ) return;
            fetch('{{ route('student.exams.answer', $exam) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ question_id: currentQ.id, selected_answer: this.answers[currentQ.id] ?? 'a', tab_switches: this.tabSwitches }),
            });
        },

        selectAnswer(opt) {
            const q = this.questions[this.currentIndex];
            if (!q) return;
            this.answers[q.id] = opt;
            this.saveAnswer(q.id, opt);
            // Auto-advance to the next question — no manual navigation.
            if (this.currentIndex < this.questions.length - 1) {
                setTimeout(() => { this.currentIndex++; }, 350);
            }
        },

        saveAnswer(questionId, answer) {
            fetch('{{ route('student.exams.answer', $exam) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ question_id: questionId, selected_answer: answer, tab_switches: this.tabSwitches }),
            });
        },

        confirmSubmit() {
            const unanswered = this.questions.length - Object.keys(this.answers).length;
            if (unanswered > 0 && !confirm(`You have ${unanswered} unanswered question${unanswered > 1 ? 's' : ''}. Submit anyway?`)) return;
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
