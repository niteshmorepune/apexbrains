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
        <p class="text-white/60 text-xs mt-1">Practice, Improve, Achieve</p>
    </div>

    {{-- Quick Actions (4 tiles) --}}
    <div class="grid grid-cols-2 gap-3">
        @foreach([
            ['route' => 'external.practice.hub',         'emoji' => '🎯', 'label' => 'Practice'],
            ['route' => 'external.practice.index',       'emoji' => '📄', 'label' => 'Practice Papers'],
            ['route' => 'external.competitions.index',   'emoji' => '🏆', 'label' => 'Competitions'],
            ['route' => 'external.certificates.index',   'emoji' => '🎓', 'label' => 'Results & Certificate'],
        ] as $action)
            <a href="{{ route($action['route']) }}"
               class="bg-white rounded-2xl border border-border p-4 flex flex-col items-center gap-2 hover:border-fran transition-colors">
                <span class="text-2xl">{{ $action['emoji'] }}</span>
                <span class="text-sm font-semibold text-gray-700 text-center">{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Recent activity --}}
    @if($recentAttempts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-700">Recent Activity</p>
                <a href="{{ route('external.results') }}" class="text-xs text-fran">View All</a>
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
                <a href="{{ route('external.results') }}" class="text-xs text-fran font-medium">View All →</a>
            </div>
        </div>
    @endif

</div>
@endsection
