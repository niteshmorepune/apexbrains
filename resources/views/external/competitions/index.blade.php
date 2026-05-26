@extends('layouts.external')
@section('title', 'Competitions')

@section('content')
<div class="p-4 space-y-4">

    {{-- My Registrations --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">My Registrations</p>
        @forelse($myRegistrations as $reg)
            <a href="{{ route('external.competitions.show', $reg->competition) }}"
               class="block bg-white rounded-2xl border border-border p-4 mb-3 hover:border-fran transition-colors">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-fran/10 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-fran" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm">{{ $reg->competition?->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            @if($reg->competition?->start_date)
                                {{ $reg->competition->start_date->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                    <span class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium flex-shrink-0">
                        Registered
                    </span>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-border p-6 text-center text-gray-400">
                <p class="text-sm">No registrations yet.</p>
                <p class="text-xs mt-1">Your franchise will register you for competitions.</p>
            </div>
        @endforelse
    </div>

    {{-- Open Competitions (view only) --}}
    @if($openCompetitions->isNotEmpty())
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Open to External Participants</p>
            @foreach($openCompetitions as $comp)
                <a href="{{ route('external.competitions.show', $comp) }}"
                   class="block bg-white rounded-2xl border border-border p-4 mb-3 hover:border-fran transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 text-sm">{{ $comp->title }}</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @if($comp->start_date)
                                    <span class="text-xs text-gray-400">{{ $comp->start_date->format('d M') }}</span>
                                @endif
                                @if($comp->fee_amount > 0)
                                    <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full">₹{{ number_format($comp->fee_amount, 0) }}</span>
                                @endif
                            </div>
                        </div>
                        @if(in_array($comp->id, $registeredIds))
                            <span class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium ml-2">Registered</span>
                        @else
                            <svg class="w-4 h-4 text-gray-300 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif

</div>
@endsection
