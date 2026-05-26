@extends('layouts.student')
@section('title', 'Exams')

@section('content')
<div class="p-4 space-y-4">

    {{-- Upcoming Exams --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Upcoming Exams</p>
        @forelse($upcomingExams as $exam)
            <a href="{{ route('student.exams.show', $exam) }}"
               class="block bg-white rounded-2xl border border-border p-4 mb-3 hover:border-fran transition-colors">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-fran/10 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="text-fran font-bold text-xs">L{{ $exam->level?->number }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm">{{ $exam->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $exam->total_questions }}Q · {{ $exam->duration_minutes }}min
                            · Pass {{ number_format($exam->pass_percentage, 0) }}%
                        </p>
                        @if($exam->scheduled_at)
                            <p class="text-xs text-fran mt-1">{{ $exam->scheduled_at->format('d M Y, H:i') }}</p>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-border p-8 text-center text-gray-400">
                <p class="text-sm">No upcoming exams scheduled.</p>
            </div>
        @endforelse
    </div>

    {{-- Past Attempts --}}
    @if($pastAttempts->isNotEmpty())
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Past Attempts</p>
            <div class="bg-white rounded-2xl border border-border overflow-hidden">
                <div class="divide-y divide-border">
                    @foreach($pastAttempts as $attempt)
                        <div class="px-4 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                                <span class="text-xs font-bold">{{ $attempt->is_passed ? '✓' : '✗' }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->exam?->title }}</p>
                                <p class="text-xs text-gray-400">{{ $attempt->submitted_at?->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-sm {{ $attempt->is_passed ? 'text-green-600' : 'text-red-500' }}">
                                    {{ number_format($attempt->percentage, 0) }}%
                                </p>
                                <p class="text-xs text-gray-400">{{ $attempt->score }}/{{ count($attempt->question_ids ?? []) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
