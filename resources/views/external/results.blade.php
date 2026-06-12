@extends('layouts.external')
@section('title', 'Results')

@section('content')
@php $borderColors = ['#D42B2B', '#F5A623', '#FFD54F', '#FF69B4', '#1A73E8', '#9C27B0']; @endphp

<x-student-header title="Results" :back="route('external.home')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-xl font-black text-fran">{{ $totalDone }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Sessions Done</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-xl font-black text-stu">{{ number_format($avgScore, 0) }}%</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Avg Accuracy</p>
        </div>
    </div>

    {{-- Past sessions --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Practice History</p>
        <div class="space-y-3">
            @forelse($sessions as $i => $session)
                <a href="{{ route('external.practice.results', $session) }}" class="block bg-white rounded-2xl border border-border overflow-hidden">
                    <div class="flex items-stretch">
                        <span class="w-1.5 flex-shrink-0" style="background-color: {{ $borderColors[$i % count($borderColors)] }}"></span>
                        <div class="flex items-center gap-3 p-4 flex-1 min-w-0">
                            <span class="w-10 h-10 rounded-full bg-bg-light flex items-center justify-center flex-shrink-0 text-base">🏆</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">{{ ucfirst($session->difficulty ?? 'Mixed') }} Practice</p>
                                <p class="text-xs text-gray-400">{{ $session->completed_at?->format('d M Y') }} · {{ $session->questions_correct ?? 0 }}/{{ $session->total_questions }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-sm font-bold {{ $session->accuracy >= 75 ? 'text-stu' : 'text-logo-amber' }}">{{ number_format($session->accuracy ?? 0, 0) }}%</span>
                                <p class="text-xs text-fran font-semibold mt-0.5">View</p>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
                    <div class="text-4xl mb-2">📚</div>
                    <p class="font-medium text-gray-600">No results yet</p>
                    <p class="text-sm mt-1">Complete a practice session to see results here.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($sessions->hasPages())
        <div class="py-2">{{ $sessions->links('pagination::tailwind') }}</div>
    @endif

</div>
@endsection
