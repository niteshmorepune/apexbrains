@extends('layouts.external')
@section('title', 'Home')

@section('content')
<div class="p-4 space-y-4">

    {{-- Welcome banner --}}
    <div class="bg-fran rounded-2xl p-5 text-white">
        <p class="text-white/70 text-sm">
            @php
                $hour = now()->hour;
                echo $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            @endphp
        </p>
        <p class="text-xl font-bold mt-0.5">{{ auth()->user()->name }}</p>
        <span class="mt-2 inline-block bg-white/20 text-white text-xs px-3 py-1 rounded-full font-medium">
            Competition Participant
        </span>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4 text-center">
            <p class="text-2xl font-black text-fran">{{ $totalPapers }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Practice papers</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4 text-center">
            <p class="text-2xl font-black text-fran">{{ $attemptedCount }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Papers attempted</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('external.practice.index') }}"
           class="bg-fran rounded-2xl p-4 text-white flex flex-col items-center gap-2">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold">Practice</span>
        </a>
        <a href="{{ route('external.competitions.index') }}"
           class="bg-white border border-border rounded-2xl p-4 flex flex-col items-center gap-2 hover:border-fran transition-colors">
            <div class="w-10 h-10 bg-fran/10 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-fran" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21H5a2 2 0 01-2-2v-1a5 5 0 015-5h8a5 5 0 015 5v1a2 2 0 01-2 2h-3M12 3a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-700">Competitions</span>
        </a>
    </div>

    {{-- Recent attempts --}}
    @if($recentAttempts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border">
                <p class="text-sm font-semibold text-gray-700">Recent Practice</p>
            </div>
            <div class="divide-y divide-border">
                @foreach($recentAttempts as $attempt)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-fran/10 flex items-center justify-center flex-shrink-0">
                            <span class="text-fran font-bold text-sm">{{ number_format($attempt->percentage, 0) }}%</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->paper?->title }}</p>
                            <p class="text-xs text-gray-400">{{ $attempt->score }}/{{ $attempt->paper?->total_questions }} · {{ $attempt->submitted_at?->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('external.practice.result', $attempt->paper) }}"
                           class="text-xs text-gray-400 hover:text-gray-600">View</a>
                    </div>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t border-border">
                <a href="{{ route('external.practice.index') }}" class="text-xs text-fran font-medium">All papers →</a>
            </div>
        </div>
    @endif

</div>
@endsection
