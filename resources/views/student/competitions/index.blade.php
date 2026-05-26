@extends('layouts.student')
@section('title', 'Competitions')

@section('content')
<div class="p-4 space-y-4">

    {{-- Practice Papers shortcut --}}
    <a href="{{ route('student.competitions.practice') }}"
       class="block bg-fran rounded-2xl p-4 text-white flex items-center gap-3">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-sm">Practice Papers</p>
            <p class="text-white/70 text-xs">Prepare with past competition papers</p>
        </div>
        <svg class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>

    {{-- Open Competitions --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Open Competitions</p>

        @if(session('success'))
            <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @forelse($competitions as $comp)
            <div class="bg-white rounded-2xl border border-border p-4 mb-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">{{ $comp->title }}</p>
                        @if($comp->description)
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $comp->description }}</p>
                        @endif
                        <div class="flex flex-wrap gap-2 mt-2">
                            @if($comp->start_date)
                                <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full">
                                    {{ $comp->start_date->format('d M') }}
                                </span>
                            @endif
                            @if($comp->registration_deadline)
                                <span class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full">
                                    Register by {{ $comp->registration_deadline->format('d M') }}
                                </span>
                            @endif
                            @if($comp->fee_amount > 0)
                                <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full">
                                    ₹{{ number_format($comp->fee_amount, 0) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if(in_array($comp->id, $myRegistrationIds))
                        <span class="text-xs bg-green-50 text-green-600 px-3 py-1.5 rounded-xl font-medium flex-shrink-0">
                            Registered ✓
                        </span>
                    @else
                        <form method="POST" action="{{ route('student.competitions.register', $comp) }}" class="flex-shrink-0">
                            @csrf
                            <button type="submit"
                                    class="text-xs bg-fran text-white px-3 py-1.5 rounded-xl font-medium hover:bg-fran-dark">
                                Register
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-border p-8 text-center text-gray-400">
                <p class="text-sm">No open competitions right now.</p>
                <p class="text-xs mt-1">Check back soon or practice with past papers.</p>
            </div>
        @endforelse
    </div>

    {{-- Past Competitions --}}
    @if($pastCompetitions->isNotEmpty())
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Past Competitions</p>
            <div class="bg-white rounded-2xl border border-border overflow-hidden">
                <div class="divide-y divide-border">
                    @foreach($pastCompetitions as $comp)
                        <div class="px-4 py-3 flex items-center gap-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">{{ $comp->title }}</p>
                                <p class="text-xs text-gray-400">{{ $comp->end_date?->format('d M Y') }}</p>
                            </div>
                            @if(in_array($comp->id, $myRegistrationIds))
                                <span class="text-xs text-gray-400">Participated</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
