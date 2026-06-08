@extends('layouts.franchise')
@section('title', 'Practice Completed')

@php
    $shown = $session->result?->total_questions_shown ?? $session->current_question_index;
    // Decorative confetti: [top%, left%, color class, shape, sizePx]
    $confetti = [
        [12, 8, 'text-emerald-400', 'diamond', 16], [18, 30, 'text-rose-400', 'star', 14],
        [40, 6, 'text-rose-400', 'star', 16], [62, 4, 'text-amber-400', 'star', 18],
        [82, 9, 'text-violet-400', 'star', 16], [30, 16, 'text-amber-400', 'squiggle', 18],
        [70, 30, 'text-blue-400', 'dot', 10], [14, 70, 'text-emerald-400', 'diamond', 14],
        [22, 88, 'text-violet-400', 'star', 18], [40, 92, 'text-rose-400', 'star', 14],
        [55, 80, 'text-emerald-500', 'square', 14], [78, 86, 'text-violet-400', 'diamond', 14],
        [86, 70, 'text-amber-400', 'squiggle', 18], [50, 70, 'text-blue-300', 'dot', 10],
    ];
@endphp

@section('content')

<div>
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-[13px] text-gray-400 mb-1">
        <a href="{{ route('franchise.class-practice.index') }}" class="hover:text-gray-600">Franchises</a>
        <span>/</span>
        <span class="font-semibold text-gray-700">Class Practice</span>
    </nav>
    <h1 class="text-[26px] font-extrabold text-gray-900 mb-5">Class Practice</h1>

    <div class="bg-white rounded-2xl border border-border shadow-sm overflow-hidden max-w-[1140px]">
        <div class="relative px-6 py-14 sm:py-16 text-center overflow-hidden">

            {{-- Confetti --}}
            @foreach($confetti as [$top, $left, $color, $shape, $size])
                <span class="pointer-events-none absolute {{ $color }}" style="top: {{ $top }}%; left: {{ $left }}%; width: {{ $size }}px; height: {{ $size }}px;">
                    @switch($shape)
                        @case('star')
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-full h-full"><path d="M12 2l2.6 6.6L21 9.3l-5 4.4L17.5 21 12 17.3 6.5 21 8 13.7l-5-4.4 6.4-.7z"/></svg>
                            @break
                        @case('diamond')
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-full h-full"><path d="M12 2l10 10-10 10L2 12z"/></svg>
                            @break
                        @case('square')
                            <span class="block w-full h-full rounded-[3px] bg-current rotate-12"></span>
                            @break
                        @case('dot')
                            <span class="block w-full h-full rounded-full bg-current"></span>
                            @break
                        @case('squiggle')
                            <svg viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-full h-full"><path d="M2 8c3-6 5 4 8 0s5 4 8 0"/></svg>
                            @break
                    @endswitch
                </span>
            @endforeach

            <div class="relative">
                {{-- Check badge --}}
                <div class="mx-auto w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-2">Practice Completed</h2>
                <p class="text-gray-500 text-base">You have successfully completed the session.</p>

                {{-- Stats --}}
                <div class="mt-8 mx-auto max-w-xl grid grid-cols-2 rounded-2xl border border-border bg-bg-light divide-x divide-border">
                    <div class="py-6 px-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1">Total Questions</p>
                        <p class="text-3xl font-extrabold text-fran">{{ $shown }}<span class="text-gray-300 text-xl font-bold">/{{ $session->total_questions }}</span></p>
                    </div>
                    <div class="py-6 px-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1">Level</p>
                        <p class="text-3xl font-extrabold text-gray-900">L{{ $session->level?->number ?? '—' }}</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-9 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <form method="POST" action="{{ route('franchise.class-practice.replay', $session) }}" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-7 py-3.5 bg-fran text-white rounded-full text-sm font-semibold hover:bg-fran-dark transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 2v6h6M3.51 9a9 9 0 102.13-3.36L3 8"/>
                            </svg>
                            Replay Same Set
                        </button>
                    </form>
                    <form method="POST" action="{{ route('franchise.class-practice.again', $session) }}" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-7 py-3.5 border border-fran text-fran rounded-full text-sm font-semibold hover:bg-blue-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Practice
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Answer Key — always visible so students can match against their notebook --}}
    <div class="max-w-[1140px] mt-5 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-border">
            <div>
                <h2 class="text-base font-bold text-gray-900">Answer Key</h2>
                <p class="text-xs text-gray-400 mt-0.5">Match these with the answers in your notebook.</p>
            </div>
            <span class="text-xs font-semibold text-fran bg-blue-50 px-3 py-1 rounded-full whitespace-nowrap">{{ $shown }} answers</span>
        </div>
        <div class="p-4 sm:p-5 grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-8 gap-2">
            @foreach($session->sessionQuestions as $sq)
                @php
                    $letter  = strtolower($sq->question->correct_answer);
                    $value   = $sq->question->{'option_' . $letter} ?? strtoupper($sq->question->correct_answer);
                    $isShown = $sq->sort_order <= $shown;
                @endphp
                <div class="flex items-center gap-2 rounded-lg border px-2.5 py-2 text-sm
                            @if($isShown) border-border bg-bg-light @else border-dashed border-border opacity-40 @endif">
                    <span class="text-xs font-bold text-gray-400 w-7 flex-shrink-0 text-right">{{ $sq->sort_order }}.</span>
                    <span class="font-bold text-green-600 truncate">{{ $value }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Collapsible question review (full text + options) --}}
    <div class="max-w-[1140px] mt-5" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-4 bg-white rounded-2xl border border-border text-sm font-semibold text-fran hover:bg-bg-light transition-colors">
            <span>Questions reviewed ({{ $session->sessionQuestions->count() }})</span>
            <svg class="w-5 h-5 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-cloak x-transition class="mt-2 bg-white rounded-2xl border border-border overflow-hidden">
            <div class="divide-y divide-border">
                @foreach($session->sessionQuestions as $sq)
                    <div class="px-5 py-4 @if($sq->sort_order > $shown) opacity-40 @endif">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                                @if($sq->sort_order <= $shown) bg-fran text-white @else bg-bg-mid text-gray-400 @endif">
                                {{ $sq->sort_order }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 font-medium">{{ $sq->question->question_text }}</p>
                                <div class="grid grid-cols-2 gap-x-6 gap-y-1 mt-2">
                                    @foreach(['a' => $sq->question->option_a, 'b' => $sq->question->option_b, 'c' => $sq->question->option_c, 'd' => $sq->question->option_d] as $letter => $val)
                                        @if($val)
                                            <p class="text-xs @if(strtolower($sq->question->correct_answer) === $letter) text-green-600 font-semibold @else text-gray-500 @endif">
                                                {{ strtoupper($letter) }}) {{ $val }}@if(strtolower($sq->question->correct_answer) === $letter) ✓ @endif
                                            </p>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @if($sq->sort_order > $shown)
                                <span class="text-xs text-gray-400 flex-shrink-0">Not shown</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
