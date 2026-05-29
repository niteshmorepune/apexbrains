@extends('layouts.external')
@section('title', 'My Results')

@section('content')
<div class="px-4 pt-4 pb-2">
    <h1 class="text-lg font-bold text-admin">My Results</h1>
    <p class="text-xs text-gray-400 mt-0.5">Practice paper attempt history</p>
</div>

{{-- Stats --}}
<div class="px-4 grid grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-border p-3 text-center">
        <p class="text-xl font-bold text-fran">{{ $totalDone }} / {{ $totalPapers }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Papers Done</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-3 text-center">
        <p class="text-xl font-bold text-stu">{{ number_format($avgScore, 1) }}%</p>
        <p class="text-xs text-gray-400 mt-0.5">Avg Score</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-3 text-center">
        <p class="text-xl font-bold text-logo-amber">{{ round($totalDone / max($totalPapers, 1) * 100) }}%</p>
        <p class="text-xs text-gray-400 mt-0.5">Progress</p>
    </div>
</div>

{{-- Attempts list --}}
<div class="px-4 space-y-3">
    @forelse($attempts as $attempt)
        <a href="{{ route('external.practice.result', $attempt->paper) }}"
           class="flex items-center gap-3 bg-white rounded-2xl border border-border p-4 hover:border-fran transition-colors block">
            <div class="w-10 h-10 rounded-xl bg-fran-light flex items-center justify-center flex-shrink-0">
                <span class="text-sm font-bold text-fran">{{ $attempt->paper?->paper_number ?? '#' }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-admin truncate">{{ $attempt->paper?->title ?? 'Practice Paper' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $attempt->submitted_at?->diffForHumans() }}</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-sm font-bold {{ $attempt->percentage >= 75 ? 'text-stu' : 'text-logo-amber' }}">
                    {{ number_format($attempt->percentage, 0) }}%
                </p>
                <p class="text-xs text-gray-400">View</p>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
            <div class="text-4xl mb-2">📚</div>
            <p class="font-medium text-gray-600">No results yet</p>
            <p class="text-sm mt-1">Complete a practice paper to see results here.</p>
        </div>
    @endforelse
</div>

@if($attempts->hasPages())
    <div class="px-4 py-4">{{ $attempts->links('pagination::tailwind') }}</div>
@endif

@endsection
