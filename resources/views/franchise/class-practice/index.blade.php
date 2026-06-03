@extends('layouts.franchise')
@section('title', 'Class Practice')
@section('page-title', 'Class Practice Sessions')

@section('page-actions')
    <a href="{{ route('franchise.class-practice.create') }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">
        + New Session
    </a>
@endsection

@php
    // Distinct colour per level number so each level is instantly recognisable.
    $levelPalette = [
        ['bg-blue-100',    'text-blue-700'],
        ['bg-emerald-100', 'text-emerald-700'],
        ['bg-amber-100',   'text-amber-700'],
        ['bg-violet-100',  'text-violet-700'],
        ['bg-rose-100',    'text-rose-700'],
        ['bg-cyan-100',    'text-cyan-700'],
        ['bg-indigo-100',  'text-indigo-700'],
        ['bg-orange-100',  'text-orange-700'],
    ];
    $levelStyle = function (?int $number) use ($levelPalette) {
        if (! $number) return ['bg-gray-100', 'text-gray-500'];
        return $levelPalette[($number - 1) % count($levelPalette)];
    };
@endphp

@section('content')

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-fran">All Sessions</h2>
        <span class="text-xs text-gray-400">{{ $sessions->total() }} total</span>
    </div>

    {{-- Level filter tabs --}}
    @if($levels->isNotEmpty())
        <div class="px-5 py-3 border-b border-border flex items-center gap-2 overflow-x-auto">
            <a href="{{ route('franchise.class-practice.index') }}"
               class="flex-shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors
                      {{ ! $activeLevel ? 'bg-fran text-white' : 'bg-bg-light text-gray-600 hover:bg-blue-50' }}">
                All Levels
            </a>
            @foreach($levels as $level)
                @php [$lbg, $ltext] = $levelStyle($level->number); @endphp
                <a href="{{ route('franchise.class-practice.index', ['level' => $level->id]) }}"
                   class="flex-shrink-0 flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors
                          {{ $activeLevel === $level->id ? 'bg-fran text-white' : 'bg-bg-light text-gray-600 hover:bg-blue-50' }}">
                    <span class="w-2 h-2 rounded-full {{ $activeLevel === $level->id ? 'bg-white/80' : $lbg }} {{ $activeLevel === $level->id ? '' : 'ring-1 ring-inset' }}"></span>
                    Level {{ $level->number }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="divide-y divide-border">
        @forelse($sessions as $session)
            @php [$lbg, $ltext] = $levelStyle($session->level?->number); @endphp
            <div class="px-5 py-4 hover:bg-bg-light flex items-center gap-4">
                {{-- Level badge --}}
                <div class="flex-shrink-0 w-12 h-12 rounded-xl {{ $lbg }} {{ $ltext }} flex flex-col items-center justify-center leading-none">
                    <span class="text-[9px] font-bold uppercase tracking-wide opacity-70">Lvl</span>
                    <span class="text-base font-extrabold">{{ $session->level?->number ?? '—' }}</span>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $session->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <span class="font-medium {{ $ltext }}">Level {{ $session->level?->number ?? '?' }}</span>
                        @if($session->level?->title)
                            · {{ $session->level->title }}
                        @endif
                        @if($session->batch)
                            · {{ $session->batch->name }}
                        @endif
                        · {{ $session->total_questions }}Q
                        · {{ $session->time_per_question_seconds }}s/Q
                    </p>
                </div>
                <div class="text-center min-w-[80px] hidden sm:block">
                    <span class="font-mono text-xs bg-bg-mid px-2 py-1 rounded-lg text-gray-600">
                        {{ $session->session_code }}
                    </span>
                </div>
                <div class="text-center min-w-[80px]">
                    @if($session->status === 'pending')
                        <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded-full">Pending</span>
                    @elseif($session->status === 'active')
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full">Live</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Ended</span>
                    @endif
                </div>
                <div class="text-xs text-gray-400 min-w-[90px] text-right hidden md:block">
                    {{ $session->created_at->format('d M Y') }}
                </div>
                <div class="flex items-center gap-2">
                    @if($session->status !== 'ended')
                        <a href="{{ route('franchise.class-practice.project', $session) }}"
                           class="text-xs bg-fran text-white px-3 py-1.5 rounded-lg font-medium hover:bg-fran-dark">
                            Project
                        </a>
                    @else
                        <a href="{{ route('franchise.class-practice.results', $session) }}"
                           class="text-xs border border-border text-gray-600 px-3 py-1.5 rounded-lg hover:bg-bg-light">
                            Results
                        </a>
                    @endif
                    <a href="{{ route('franchise.class-practice.show', $session) }}"
                       class="text-xs text-gray-400 hover:text-gray-600">View</a>
                </div>
            </div>
        @empty
            <div class="px-5 py-16 text-center text-gray-400">
                @if($activeLevel)
                    <p class="text-base mb-2">No sessions for this level</p>
                    <p class="text-sm">Try a different level or
                        <a href="{{ route('franchise.class-practice.index') }}" class="text-fran font-medium hover:underline">view all sessions</a>.
                    </p>
                @else
                    <p class="text-base mb-2">No sessions yet</p>
                    <p class="text-sm">Create a session to start practising questions with your class.</p>
                    <a href="{{ route('franchise.class-practice.create') }}"
                       class="mt-4 inline-block px-5 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold">
                        New Session
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    @if($sessions->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $sessions->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
