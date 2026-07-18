<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Competition Practice — Apex Brains</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg-light font-sans min-h-screen"
      x-data="practiceEngine()"
      x-init="init()">

    {{-- Header --}}
    <div class="bg-fran text-white px-4 py-3 flex items-center justify-between sticky top-0 z-20">
        <div>
            <p class="font-semibold text-sm">Competition Practice</p>
            <p class="text-white/70 text-xs" x-text="`Q${currentIndex + 1} of ${questions.length}`"></p>
        </div>
        <div class="text-center">
            <p class="text-xl font-black tabular-nums" :class="{ 'text-red-200': remaining <= 30 }" x-text="formatTime(remaining)"></p>
            <p class="text-white/60 text-xs">remaining</p>
        </div>
    </div>

    <div class="h-1 bg-white/20">
        <div class="h-full bg-white transition-all"
             :style="`width: ${(currentIndex / questions.length) * 100}%`"></div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-6 space-y-4">
        <template x-if="questions.length > 0">
            <div>
                <div class="bg-white rounded-2xl border border-border p-5 mb-4 text-center">
                    <div class="text-gray-900" style="font-size:30px"
                         x-html="verticalSum(questions[currentIndex]?.question_text)"></div>
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
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
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

                <div class="flex items-center justify-between mt-5">
                    <button @click="confirmSubmit()" class="px-5 py-2 border border-green-600 text-green-600 rounded-xl text-sm font-semibold hover:bg-green-50">
                        Submit
                    </button>
                    <template x-if="currentIndex < questions.length - 1">
                        <button @click="next()" class="px-5 py-2 bg-fran text-white rounded-xl text-sm font-semibold">
                            Next →
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <form id="submitForm" method="POST" action="{{ route('external.practice.submit', $attempt) }}" class="hidden">
        @csrf
    </form>

</body>

<script>
function practiceEngine() {
    return {
        questions: @json($questions),
        answers: @json(array_map(fn($v) => $v, $savedAnswers ?: [])),
        currentIndex: 0,
        remaining: {{ $remaining }},

        init() {
            this.startTimer();
        },

        startTimer() {
            setInterval(() => {
                if (this.remaining > 0) {
                    this.remaining--;
                } else {
                    this.doSubmit();
                }
            }, 1000);
        },

        formatTime(secs) {
            secs = Math.max(0, Math.floor(secs));
            const m = Math.floor(secs / 60);
            const s = secs % 60;
            return `${m}:${s.toString().padStart(2, '0')}`;
        },

        next() {
            this.currentIndex++;
        },

        selectAnswer(opt) {
            const q = this.questions[this.currentIndex];
            if (!q) return;
            this.answers[q.id] = opt;
            fetch('{{ route('external.practice.answer', $attempt) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ question_id: q.id, selected_answer: opt }),
            });
        },

        doSubmit() {
            document.getElementById('submitForm').submit();
        },

        confirmSubmit() {
            const unanswered = this.questions.length - Object.keys(this.answers).length;
            if (unanswered > 0 && !confirm(`You still have ${unanswered} unanswered question(s). Submit anyway?`)) {
                return;
            }
            this.doSubmit();
        },

        // Render an arithmetic expression as a right-aligned vertical column
        // (abacus sum layout). Numbers stack; operators sit to the left.
        verticalSum(text) {
            if (!text) return '';
            const clean = String(text).replace(/=\s*\?|\?/g, '');
            const opMap = { '*': '×', 'x': '×', 'X': '×', '/': '÷', '-': '−', '–': '−' };
            const re = /([+\-−–×xX*÷/])?\s*(\d+(?:\.\d+)?)/g;
            const rows = []; let m, first = true;
            while ((m = re.exec(clean)) !== null) {
                let op = m[1] || '';
                if (op && opMap[op]) op = opMap[op];
                if (first) op = ''; else if (!op) op = '+';
                rows.push(`<tr><td style="text-align:right;padding-right:0.6em;color:#9ca3af">${op}</td><td style="text-align:right;font-variant-numeric:tabular-nums">${m[2]}</td></tr>`);
                first = false;
            }
            if (rows.length <= 1) return `<span style="font-weight:900">${text}</span>`;
            return `<table style="border-collapse:collapse;margin:0 auto;font-family:monospace;font-weight:900">${rows.join('')}`
                 + `<tr><td colspan="2" style="border-top:4px solid #1f2937;padding-top:4px"></td></tr></table>`;
        },
    };
}
</script>
</html>
