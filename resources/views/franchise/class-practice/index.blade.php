@extends('layouts.franchise')
@section('title', 'Class Practice')
@section('page-title', 'Class Practice Sessions')

@section('page-actions')
    <a href="{{ route('franchise.class-practice.create') }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">
        + New Session
    </a>
@endsection

@section('content')

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-fran">All Sessions</h2>
        <span class="text-xs text-gray-400">{{ $sessions->total() }} total</span>
    </div>

    <div class="divide-y divide-border">
        @forelse($sessions as $session)
            <div class="px-5 py-4 hover:bg-bg-light flex items-center gap-4">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm">{{ $session->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Level {{ $session->level?->number }}
                        @if($session->batch)
                            · {{ $session->batch->name }}
                        @endif
                        · {{ $session->total_questions }}Q
                        · {{ $session->time_per_question_seconds }}s/Q
                    </p>
                </div>
                <div class="text-center min-w-[80px]">
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
                <div class="text-xs text-gray-400 min-w-[90px] text-right">
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
                <p class="text-base mb-2">No sessions yet</p>
                <p class="text-sm">Create a session to start practising questions with your class.</p>
                <a href="{{ route('franchise.class-practice.create') }}"
                   class="mt-4 inline-block px-5 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold">
                    New Session
                </a>
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
