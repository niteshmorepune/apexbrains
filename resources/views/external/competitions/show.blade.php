@extends('layouts.external')
@section('title', $competition->title)

@section('content')
<div class="p-4 space-y-4">

    {{-- Competition card --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="font-bold text-gray-800 text-base">{{ $competition->title }}</p>
        @if($competition->description)
            <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $competition->description }}</p>
        @endif

        <div class="grid grid-cols-2 gap-3 mt-4">
            @if($competition->start_date)
                <div class="bg-bg-light rounded-xl p-3">
                    <p class="text-xs text-gray-400">Start Date</p>
                    <p class="font-semibold text-sm mt-0.5">{{ $competition->start_date->format('d M Y') }}</p>
                </div>
            @endif
            @if($competition->end_date)
                <div class="bg-bg-light rounded-xl p-3">
                    <p class="text-xs text-gray-400">End Date</p>
                    <p class="font-semibold text-sm mt-0.5">{{ $competition->end_date->format('d M Y') }}</p>
                </div>
            @endif
            @if($competition->registration_deadline)
                <div class="bg-bg-light rounded-xl p-3">
                    <p class="text-xs text-gray-400">Register By</p>
                    <p class="font-semibold text-sm mt-0.5">{{ $competition->registration_deadline->format('d M Y') }}</p>
                </div>
            @endif
            @if($competition->fee_amount > 0)
                <div class="bg-bg-light rounded-xl p-3">
                    <p class="text-xs text-gray-400">Entry Fee</p>
                    <p class="font-semibold text-sm mt-0.5">₹{{ number_format($competition->fee_amount, 0) }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Registration status --}}
    @if($myRegistration)
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
            <p class="text-green-700 font-semibold text-sm">✓ You are registered</p>
            @if($myRegistration->registration_date)
                <p class="text-green-600 text-xs mt-1">Since {{ $myRegistration->registration_date->format('d M Y') }}</p>
            @endif
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
            <p class="text-amber-700 text-sm font-medium">Not registered</p>
            <p class="text-amber-600 text-xs mt-1">Contact your franchise to register for this competition.</p>
        </div>
    @endif

    <a href="{{ route('external.competitions.index') }}"
       class="block text-center text-sm text-gray-400 hover:text-gray-600 py-2">
        ← Back to Competitions
    </a>

</div>
@endsection
