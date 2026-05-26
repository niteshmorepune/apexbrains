@extends('layouts.student')
@section('title', 'Practice — Q' . ($index + 1))

@section('content')
<div class="p-4 space-y-4" x-data="{ selected: null, submitting: false }">

    {{-- Progress bar --}}
    <div>
        <div class="flex justify-between text-xs text-gray-500 mb-1.5">
            <span>Question {{ $index + 1 }} of {{ $totalCount }}</span>
            <span>{{ count($answered) }} answered</span>
        </div>
        <div class="w-full h-2 bg-bg-mid rounded-full overflow-hidden">
            <div class="h-full bg-stu rounded-full transition-all"
                 style="width: {{ ($index / max($totalCount, 1)) * 100 }}%"></div>
        </div>
    </div>

    {{-- Question card --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-sm font-bold text-gray-800 leading-relaxed">
            {{ $question['question_text'] }}
        </p>
    </div>

    {{-- Answer options --}}
    <form method="POST" action="{{ route('student.practice.answer', $session) }}" id="answerForm">
        @csrf
        <div class="space-y-3">
            @foreach(['a' => $question['option_a'], 'b' => $question['option_b'], 'c' => $question['option_c'], 'd' => $question['option_d']] as $letter => $option)
                @if($option)
                    <label class="block cursor-pointer">
                        <input type="radio" name="answer" value="{{ $letter }}" class="sr-only peer"
                               x-model="selected" @change="submitting = true; $nextTick(() => document.getElementById('answerForm').submit())">
                        <div class="flex items-center gap-3 bg-white border-2 rounded-2xl px-4 py-3.5 transition-all
                                    border-border peer-checked:border-stu peer-checked:bg-stu/5">
                            <span class="w-7 h-7 rounded-full border-2 border-border flex items-center justify-center
                                         text-xs font-bold text-gray-500 flex-shrink-0
                                         peer-checked:border-stu peer-checked:bg-stu peer-checked:text-white">
                                {{ strtoupper($letter) }}
                            </span>
                            <span class="text-sm text-gray-700">{{ $option }}</span>
                        </div>
                    </label>
                @endif
            @endforeach
        </div>
    </form>

    {{-- Skip / End --}}
    <div class="flex items-center justify-between pt-2">
        <form method="POST" action="{{ route('student.practice.submit', $session) }}">
            @csrf
            <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">End session</button>
        </form>
        <p class="text-xs text-gray-400">Tap an option to continue</p>
    </div>

</div>
@endsection
