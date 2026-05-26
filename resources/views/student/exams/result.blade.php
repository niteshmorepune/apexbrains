@extends('layouts.student')
@section('title', 'Exam Result')

@section('content')
<div class="p-4 space-y-4">

    {{-- Result banner --}}
    <div class="rounded-2xl p-6 text-white text-center
        {{ $attempt->is_passed ? 'bg-stu' : 'bg-red-500' }}">
        <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3">
            <span class="text-2xl">{{ $attempt->is_passed ? '🎉' : '😔' }}</span>
        </div>
        <p class="text-white/80 text-sm mb-1">{{ $attempt->is_passed ? 'Congratulations! You passed!' : 'Better luck next time' }}</p>
        <p class="text-4xl font-black mb-1">{{ number_format($attempt->percentage, 0) }}%</p>
        <p class="text-white/70 text-sm">{{ $attempt->score }} correct out of {{ count($attempt->question_ids ?? []) }}</p>

        <div class="mt-4 flex items-center justify-center gap-4 text-sm">
            <div class="text-center">
                <p class="font-bold">{{ number_format($exam->pass_percentage, 0) }}%</p>
                <p class="text-white/60 text-xs">Pass mark</p>
            </div>
            @if($attempt->tab_switch_count > 0)
                <div class="w-px h-8 bg-white/20"></div>
                <div class="text-center">
                    <p class="font-bold text-red-200">{{ $attempt->tab_switch_count }}</p>
                    <p class="text-white/60 text-xs">Violations</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Answer review --}}
    <div class="bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-4 py-3 border-b border-border">
            <p class="text-sm font-semibold text-gray-700">Answer Review</p>
        </div>
        <div class="divide-y divide-border">
            @foreach($attempt->answers->sortBy(fn($a) => array_search($a->question_id, $attempt->question_ids ?? [])) as $i => $answer)
                <div class="px-4 py-3">
                    <div class="flex items-start gap-2 mb-2">
                        <span class="text-xs text-gray-400 flex-shrink-0 mt-0.5">{{ $i + 1 }}.</span>
                        <p class="text-sm text-gray-800">{{ $answer->question?->question_text }}</p>
                        <span class="flex-shrink-0 ml-auto">
                            @if($answer->is_correct)
                                <span class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded-full font-medium">✓</span>
                            @else
                                <span class="text-xs bg-red-100 text-red-500 px-2 py-0.5 rounded-full font-medium">✗</span>
                            @endif
                        </span>
                    </div>
                    <div class="ml-4 text-xs space-y-0.5">
                        <p class="text-gray-500">
                            Your answer: <span class="font-medium {{ $answer->is_correct ? 'text-green-600' : 'text-red-500' }}">
                                {{ strtoupper($answer->selected_answer) }})
                                {{ $answer->question?->{'option_' . $answer->selected_answer} }}
                            </span>
                        </p>
                        @if(!$answer->is_correct)
                            <p class="text-gray-500">
                                Correct: <span class="font-medium text-green-600">
                                    {{ strtoupper($answer->question?->correct_answer) }})
                                    {{ $answer->question?->{'option_' . $answer->question?->correct_answer} }}
                                </span>
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-3">
        <a href="{{ route('student.exams.index') }}"
           class="block w-full py-3 border border-border text-gray-700 rounded-2xl text-sm font-semibold text-center hover:bg-bg-light">
            ← Back to Exams
        </a>
        <a href="{{ route('student.home') }}"
           class="block w-full py-3 bg-fran text-white rounded-2xl text-sm font-semibold text-center">
            Home
        </a>
    </div>

</div>
@endsection
