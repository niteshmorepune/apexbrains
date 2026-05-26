@extends('layouts.franchise')
@section('title', 'Session Results — ' . $session->title)
@section('page-title', 'Session Results')

@section('page-actions')
    <a href="{{ route('franchise.class-practice.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← All Sessions
    </a>
@endsection

@section('content')

<div class="grid grid-cols-3 gap-5">

    {{-- Summary Card --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Session Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Title</span>
                    <span class="font-medium text-right max-w-[160px]">{{ $session->title }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Code</span>
                    <span class="font-mono font-bold text-fran">{{ $session->session_code }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Level</span>
                    <span class="font-medium">Level {{ $session->level?->number }}</span>
                </div>
                @if($session->batch)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Batch</span>
                        <span class="font-medium">{{ $session->batch->name }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Category</span>
                    <span class="font-medium capitalize">{{ str_replace('_', ' ', $session->question_category) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Questions shown</span>
                    <span class="font-medium">{{ $session->result?->total_questions_shown ?? $session->current_question_index }} / {{ $session->total_questions }}</span>
                </div>
                @if($session->started_at && $session->ended_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Duration</span>
                        <span class="font-medium">
                            {{ gmdate('G\h i\m', $session->started_at->diffInSeconds($session->ended_at)) }}
                        </span>
                    </div>
                @endif
                @if($session->result?->completed_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Completed</span>
                        <span class="text-gray-600">{{ $session->result->completed_at->format('d M Y, H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="bg-fran rounded-2xl p-5 text-white">
            <p class="text-white/60 text-xs mb-1">Coverage</p>
            <p class="text-3xl font-black mb-1">
                {{ $session->total_questions > 0
                    ? round((($session->result?->total_questions_shown ?? $session->current_question_index) / $session->total_questions) * 100)
                    : 0 }}%
            </p>
            <p class="text-white/60 text-xs">of planned questions covered</p>
        </div>
    </div>

    {{-- Question Review --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-fran">Questions Reviewed</h2>
        </div>

        <div class="divide-y divide-border">
            @php $shown = $session->result?->total_questions_shown ?? $session->current_question_index; @endphp
            @foreach($session->sessionQuestions as $sq)
                <div class="px-5 py-4 @if($sq->sort_order > $shown) opacity-40 @endif">
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                            @if($sq->sort_order <= $shown) bg-fran text-white @else bg-bg-mid text-gray-400 @endif">
                            {{ $sq->sort_order }}
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-800 font-medium">{{ $sq->question->question_text }}</p>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-1 mt-2">
                                @foreach(['a' => $sq->question->option_a, 'b' => $sq->question->option_b, 'c' => $sq->question->option_c, 'd' => $sq->question->option_d] as $letter => $val)
                                    @if($val)
                                        <p class="text-xs @if(strtolower($sq->question->correct_answer) === $letter) text-green-600 font-semibold @else text-gray-500 @endif">
                                            {{ strtoupper($letter) }}) {{ $val }}
                                            @if(strtolower($sq->question->correct_answer) === $letter)
                                                ✓
                                            @endif
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

@endsection
