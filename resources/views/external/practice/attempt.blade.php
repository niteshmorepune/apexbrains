<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $paper->title }} — Apex Brains Competition</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg-light font-sans min-h-screen"
      x-data="paperEngine()"
      x-init="init()">

    {{-- Time up overlay --}}
    <div x-show="timeUp" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-6" x-cloak>
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center">
            <p class="font-bold text-gray-800 mb-2">Time's Up!</p>
            <p class="text-sm text-gray-500 mb-4">Submitting your answers...</p>
            <div class="w-6 h-6 border-2 border-fran border-t-transparent rounded-full animate-spin mx-auto"></div>
        </div>
    </div>

    {{-- Header --}}
    <div class="bg-fran text-white px-4 py-3 flex items-center justify-between sticky top-0 z-20">
        <div>
            <p class="font-semibold text-sm">{{ $paper->title }}</p>
            <p class="text-white/70 text-xs" x-text="`Q${currentIndex + 1} of ${questions.length}`"></p>
        </div>
        <div class="text-center">
            <p class="text-xl font-black tabular-nums" :class="timeLeft <= 60 ? 'text-red-300' : ''" x-text="formatTime(timeLeft)"></p>
            <p class="text-white/60 text-xs">remaining</p>
        </div>
    </div>

    <div class="h-1.5 bg-white/20">
        <div class="h-full bg-white transition-all"
             :style="`width: ${(currentIndex / questions.length) * 100}%`"></div>
    </div>

    {{-- Inline warning --}}
    <div class="bg-yellow-50 border-b border-yellow-200 px-4 py-2 flex items-center gap-2">
        <span class="text-yellow-500 text-xs">⚠</span>
        <p class="text-xs text-yellow-700 font-medium">Do not switch tabs — session will be flagged</p>
    </div>

    <div class="max-w-lg mx-auto px-4 py-6 space-y-4">
        <template x-if="questions.length > 0">
            <div>
                {{-- Flash Anzan: show large number for audio/anzan type questions --}}
                <template x-if="questions[currentIndex]?.question?.question_type === 'audio' || questions[currentIndex]?.question?.question_type === 'anzan'">
                    <div class="bg-white rounded-2xl border border-border p-8 text-center mb-4">
                        <p class="font-black text-gray-900 leading-none" style="font-size:100px;line-height:1"
                           x-text="questions[currentIndex]?.question?.question_text"></p>
                    </div>
                </template>
                <template x-if="!(questions[currentIndex]?.question?.question_type === 'audio' || questions[currentIndex]?.question?.question_type === 'anzan')">
                    <div class="bg-white rounded-2xl border border-border p-5 mb-4">
                        <p class="text-base font-bold text-gray-800 leading-relaxed"
                           x-text="questions[currentIndex]?.question?.question_text"></p>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="opt in ['a','b','c','d']" :key="opt">
                        <template x-if="questions[currentIndex]?.question?.['option_' + opt]">
                            <button @click="selectAnswer(opt)"
                                    :class="{
                                        'border-fran bg-fran/5': answers[questions[currentIndex]?.question?.id] === opt,
                                        'border-border bg-white': answers[questions[currentIndex]?.question?.id] !== opt
                                    }"
                                    class="w-full flex items-center gap-3 rounded-2xl border-2 px-4 py-3.5 text-left transition-colors">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                      :class="{
                                          'bg-fran text-white': answers[questions[currentIndex]?.question?.id] === opt,
                                          'bg-bg-mid text-gray-500': answers[questions[currentIndex]?.question?.id] !== opt
                                      }"
                                      x-text="opt.toUpperCase()"></span>
                                <span class="text-sm text-gray-700" x-text="questions[currentIndex]?.question?.['option_' + opt]"></span>
                            </button>
                        </template>
                    </template>
                </div>

                <div class="flex items-center justify-between mt-5">
                    <button @click="currentIndex--" x-show="currentIndex > 0"
                            class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600">← Prev</button>
                    <div x-show="currentIndex === 0"></div>

                    <template x-if="currentIndex < questions.length - 1">
                        <button @click="currentIndex++" class="px-5 py-2 bg-fran text-white rounded-xl text-sm font-semibold">
                            Next →
                        </button>
                    </template>
                    <template x-if="currentIndex === questions.length - 1">
                        <button @click="confirmSubmit()" class="px-5 py-2 bg-green-600 text-white rounded-xl text-sm font-semibold">
                            Submit
                        </button>
                    </template>
                </div>

                <div class="flex flex-wrap gap-1.5 mt-4 justify-center">
                    <template x-for="(q, i) in questions" :key="i">
                        <button @click="currentIndex = i"
                                class="w-7 h-7 rounded-lg text-xs font-bold"
                                :class="{
                                    'bg-fran text-white': i === currentIndex,
                                    'bg-green-500 text-white': answers[q.question?.id] && i !== currentIndex,
                                    'bg-bg-mid text-gray-500': !answers[q.question?.id] && i !== currentIndex
                                }"
                                x-text="i + 1"></button>
                    </template>
                </div>

                <p class="text-center text-xs text-gray-400 mt-2">
                    <span x-text="Object.keys(answers).length"></span> of <span x-text="questions.length"></span> answered
                </p>
            </div>
        </template>
    </div>

    <form id="submitForm" method="POST" action="{{ route('external.practice.submit', $paper) }}" class="hidden">
        @csrf
    </form>

</body>
<script>
function paperEngine() {
    return {
        questions: @json($questions),
        answers: @json((object)($savedAnswers ?: [])),
        currentIndex: 0,
        timeLeft: {{ $remaining }},
        timeUp: false,

        init() { this.startTimer(); },

        startTimer() {
            const tick = setInterval(() => {
                if (this.timeLeft > 0) { this.timeLeft--; }
                else { clearInterval(tick); this.timeUp = true; setTimeout(() => this.doSubmit(), 2000); }
            }, 1000);
        },

        formatTime(secs) {
            return String(Math.floor(secs / 60)).padStart(2,'0') + ':' + String(secs % 60).padStart(2,'0');
        },

        selectAnswer(opt) {
            const q = this.questions[this.currentIndex];
            if (!q?.question) return;
            this.answers[q.question.id] = opt;
            fetch('{{ route('external.practice.answer', $paper) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ question_id: q.question.id, selected_answer: opt }),
            });
        },

        confirmSubmit() {
            const unanswered = this.questions.length - Object.keys(this.answers).length;
            if (unanswered > 0 && !confirm(`${unanswered} question${unanswered > 1 ? 's' : ''} unanswered. Submit anyway?`)) return;
            this.doSubmit();
        },

        doSubmit() { document.getElementById('submitForm').submit(); },
    };
}
</script>
</html>
