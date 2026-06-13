<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $competition->title }} — Apex Brains</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{font-family:'Inter',ui-sans-serif,system-ui,sans-serif}[x-cloak]{display:none!important}</style>
</head>
<body class="bg-stu-bg min-h-screen" x-data="examEngine()" x-init="init()">

<div class="max-w-md mx-auto min-h-screen">

    {{-- Time up overlay --}}
    <div x-show="timeUp" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-6" x-cloak>
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center">
            <p class="font-bold text-gray-800 mb-2">Time's Up!</p>
            <p class="text-sm text-gray-500 mb-4">Submitting your answers...</p>
            <div class="w-6 h-6 border-2 border-fran border-t-transparent rounded-full animate-spin mx-auto"></div>
        </div>
    </div>

    {{-- Header --}}
    <div class="px-4 pt-5 pb-2 flex items-center gap-2">
        <button @click="doSubmit()" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900 truncate">{{ $competition->title }}</h1>
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
            {{-- Question strip --}}
            <div class="px-4 mt-3 overflow-x-auto">
                <div class="flex gap-2 w-max">
                    <template x-for="(q, i) in questions" :key="i">
                        <button @click="currentIndex = i" class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                :class="i === currentIndex ? 'bg-fran text-white' : (answers[q.question?.id] ? 'bg-stu-light text-stu' : 'bg-white border border-border text-gray-400')"
                                x-text="i + 1"></button>
                    </template>
                </div>
            </div>

            {{-- Calculate prompt --}}
            <div class="px-4 mt-5 flex items-center justify-between">
                <p class="text-sm text-gray-500">Calculate mentally :</p>
                <span class="text-gray-400">🔊</span>
            </div>

            {{-- Big number display --}}
            <div class="px-4 mt-3">
                <div class="bg-white rounded-2xl border border-border py-10 px-4 text-center min-h-[170px] flex items-center justify-center">
                    <div class="text-gray-900" style="font-size:38px" x-html="verticalSum(questions[currentIndex]?.question?.question_text)"></div>
                </div>
            </div>

            {{-- Answer grid --}}
            <div class="px-4 mt-5">
                <p class="text-sm text-gray-500 mb-2">Select your answer :</p>
                <div class="grid grid-cols-2 gap-3">
                    <template x-for="opt in ['a','b','c','d']" :key="opt">
                        <template x-if="questions[currentIndex]?.question?.['option_' + opt]">
                            <button @click="selectAnswer(opt)" class="flex items-center gap-3 rounded-2xl border-2 px-4 py-4 text-left"
                                    :class="answers[questions[currentIndex]?.question?.id] === opt ? 'border-stu bg-stu-light' : 'border-border bg-white'">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                                      :class="answers[questions[currentIndex]?.question?.id] === opt ? 'bg-stu text-white' : 'bg-bg-mid text-gray-500'"
                                      x-text="opt.toUpperCase()"></span>
                                <span class="text-base font-bold text-gray-800" x-text="questions[currentIndex]?.question?.['option_' + opt]"></span>
                            </button>
                        </template>
                    </template>
                </div>
            </div>

            {{-- Submit on last question only --}}
            <div class="px-4 mt-5 min-h-[52px]">
                <template x-if="currentIndex === questions.length - 1">
                    <button @click="doSubmit()" class="w-full py-3 bg-stu text-white rounded-xl text-sm font-bold">Submit</button>
                </template>
            </div>
            <div class="pb-6"></div>
        </div>
    </template>

</div>

<form id="submitForm" method="POST" action="{{ route('student.competitions.submit', $competition) }}" class="hidden">@csrf</form>

</body>
<script>
function examEngine() {
    return {
        questions: @json($questions),
        answers: @json(array_map(fn($v) => $v, $savedAnswers ?: [])),
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
            secs = Math.floor(secs);
            const m = Math.floor(secs / 60);
            const s = secs % 60;
            return `${m}:${s.toString().padStart(2, '0')}`;
        },

        selectAnswer(opt) {
            const q = this.questions[this.currentIndex];
            if (!q?.question) return;
            this.answers[q.question.id] = opt;
            fetch('{{ route('student.competitions.answer', $competition) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ question_id: q.question.id, selected_answer: opt }),
            });
            if (this.currentIndex < this.questions.length - 1) {
                setTimeout(() => { this.currentIndex++; }, 350);
            }
        },

        doSubmit() { document.getElementById('submitForm').submit(); },

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
