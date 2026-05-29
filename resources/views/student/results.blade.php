@extends('layouts.student')
@section('title', 'My Results')

@section('content')
<div class="px-4 pt-4 pb-2">
    <h1 class="text-lg font-bold text-admin">My Results</h1>
    <p class="text-xs text-gray-400 mt-0.5">All exam & practice attempt history</p>
</div>

{{-- Stats summary --}}
<div class="px-4 grid grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-border p-3 text-center">
        <p class="text-xl font-bold text-stu">{{ $totalExams }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Total Exams</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-3 text-center">
        <p class="text-xl font-bold text-fran">{{ number_format($avgScore, 1) }}%</p>
        <p class="text-xs text-gray-400 mt-0.5">Avg Score</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-3 text-center">
        <p class="text-xl font-bold text-logo-amber">{{ $passed }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Passed</p>
    </div>
</div>

{{-- Attempts list --}}
<div class="px-4 space-y-3">
    @forelse($attempts as $attempt)
        <a href="{{ route('student.exams.result', $attempt->exam) }}"
           class="flex items-center gap-3 bg-white rounded-2xl border border-border p-4 hover:border-stu transition-colors block">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ $attempt->is_passed ? 'bg-stu-light' : 'bg-red-50' }}">
                <span class="text-sm font-bold {{ $attempt->is_passed ? 'text-stu' : 'text-red-500' }}">
                    {{ $attempt->is_passed ? '✓' : '✗' }}
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-admin truncate">{{ $attempt->exam?->title ?? 'Exam' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $attempt->exam?->level ? 'L'.$attempt->exam->level->number.' · ' : '' }}
                    {{ $attempt->submitted_at?->diffForHumans() }}
                </p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-sm font-bold {{ $attempt->percentage >= 75 ? 'text-stu' : 'text-red-500' }}">
                    {{ number_format($attempt->percentage, 0) }}%
                </p>
                <p class="text-xs {{ $attempt->is_passed ? 'text-stu' : 'text-red-500' }}">
                    {{ $attempt->is_passed ? 'Pass' : 'Fail' }}
                </p>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
            <div class="text-4xl mb-2">📝</div>
            <p class="font-medium text-gray-600">No results yet</p>
            <p class="text-sm mt-1">Complete an exam to see your results here.</p>
        </div>
    @endforelse
</div>

@if($attempts->hasPages())
    <div class="px-4 py-4">{{ $attempts->links('pagination::tailwind') }}</div>
@endif

@endsection
