@extends('layouts.student')
@section('title', $exam->title)

@section('content')
<div class="p-4 space-y-4">

    {{-- Exam info card --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="font-bold text-gray-800 text-base">{{ $exam->title }}</p>
        @if($exam->description)
            <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ $exam->description }}</p>
        @endif

        <div class="grid grid-cols-2 gap-3 mt-4">
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="text-xl font-black text-gray-800">{{ $exam->total_questions }}</p>
                <p class="text-xs text-gray-500">Questions</p>
            </div>
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="text-xl font-black text-gray-800">{{ $exam->duration_minutes }}</p>
                <p class="text-xs text-gray-500">Minutes</p>
            </div>
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="text-xl font-black text-fran">{{ number_format($exam->pass_percentage, 0) }}%</p>
                <p class="text-xs text-gray-500">Pass Mark</p>
            </div>
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="text-xl font-black text-gray-800">
                    {{ $exam->max_attempts ?? '∞' }}
                </p>
                <p class="text-xs text-gray-500">Max Attempts</p>
            </div>
        </div>

        @if($exam->scheduled_at)
            <div class="mt-4 pt-3 border-t border-border">
                <p class="text-xs text-gray-500">Scheduled: <span class="font-medium text-fran">{{ $exam->scheduled_at->format('d M Y, H:i') }}</span></p>
            </div>
        @endif
    </div>

    {{-- Instructions --}}
    <div class="bg-amber-50 rounded-2xl border border-amber-200 p-4">
        <p class="text-xs font-bold text-amber-700 mb-2">Before you start</p>
        <ul class="text-xs text-amber-700 space-y-1">
            <li>• The exam will enter fullscreen mode</li>
            <li>• Do not switch tabs — it will be recorded</li>
            <li>• Your answers are saved automatically</li>
            <li>• Submit before the timer runs out</li>
        </ul>
    </div>

    {{-- Action --}}
    @if($inProgress)
        <a href="{{ route('student.exams.attempt', $exam) }}"
           class="block w-full py-3.5 bg-amber-500 text-white rounded-2xl text-sm font-semibold text-center">
            Resume Attempt →
        </a>
    @elseif($canAttempt)
        <form method="POST" action="{{ route('student.exams.start', $exam) }}">
            @csrf
            <button type="submit"
                    class="w-full py-3.5 bg-fran text-white rounded-2xl text-sm font-semibold hover:bg-fran-dark">
                Start Exam →
            </button>
        </form>
    @else
        <div class="py-3 text-center text-sm text-gray-400">
            No more attempts available.
        </div>
    @endif

    {{-- Attempt history --}}
    @if($attempts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border">
                <p class="text-sm font-semibold text-gray-700">Your Attempts</p>
            </div>
            <div class="divide-y divide-border">
                @foreach($attempts as $attempt)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                            {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                            {{ $loop->iteration }}
                        </div>
                        <div class="flex-1 text-xs text-gray-500">
                            {{ $attempt->submitted_at?->format('d M Y, H:i') }}
                            @if($attempt->tab_switch_count > 0)
                                · <span class="text-amber-500">{{ $attempt->tab_switch_count }} tab switch{{ $attempt->tab_switch_count > 1 ? 'es' : '' }}</span>
                            @endif
                        </div>
                        <span class="font-bold text-sm {{ $attempt->is_passed ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($attempt->percentage, 0) }}%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <a href="{{ route('student.exams.index') }}" class="block text-center text-sm text-gray-400 hover:text-gray-600 py-2">
        ← All Exams
    </a>

</div>
@endsection
