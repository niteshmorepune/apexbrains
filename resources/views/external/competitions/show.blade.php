@extends('layouts.external')
@section('title', $competition->title)

@section('content')
<x-student-header :title="$competition->title" :back="route('external.competitions.index')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Header card --}}
    <div class="bg-white rounded-2xl border border-border p-5 text-center">
        <span class="text-3xl">🏆</span>
        <p class="font-black text-gray-900 text-lg mt-1">{{ $competition->title }}</p>
        <div class="flex items-center justify-center gap-3 text-xs text-gray-500 mt-2">
            @if($competition->start_date)<span>📅 {{ $competition->start_date->format('d M Y') }}</span>@endif
            @if($competition->fee_amount > 0)<span>💳 ₹{{ number_format($competition->fee_amount, 0) }}</span>@else<span>🎟️ Free entry</span>@endif
        </div>
        @if($competition->description)
            <p class="text-xs text-gray-400 mt-2">{{ $competition->description }}</p>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4"><p class="text-xs text-gray-400">📅 Start</p><p class="text-base font-black text-gray-800 mt-1">{{ $competition->start_date?->format('d M') ?? 'TBA' }}</p></div>
        <div class="bg-white rounded-2xl border border-border p-4"><p class="text-xs text-gray-400">🏁 End</p><p class="text-base font-black text-gray-800 mt-1">{{ $competition->end_date?->format('d M') ?? 'TBA' }}</p></div>
        <div class="bg-white rounded-2xl border border-border p-4"><p class="text-xs text-gray-400">🌐 Type</p><p class="text-base font-black text-gray-800 mt-1 capitalize">{{ $competition->competition_type }}</p></div>
        <div class="bg-white rounded-2xl border border-border p-4"><p class="text-xs text-gray-400">🎟️ Entry</p><p class="text-base font-black text-gray-800 mt-1">@if($competition->fee_amount > 0)₹{{ number_format($competition->fee_amount, 0) }}@else Free @endif</p></div>
    </div>

    {{-- Rules --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-sm font-bold text-gray-800 mb-3">Rules and Instructions</p>
        <ol class="space-y-2.5">
            @foreach([
                'Registration is handled by your branch / academy.',
                'Use the practice papers to prepare beforehand.',
                'Arrive / log in before the competition start time.',
                'Results and certificates are issued after the event.',
                'In case of technical issues, contact your branch.',
            ] as $i => $rule)
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-fran-light text-fran text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i + 1 }}</span>
                    <span class="text-sm text-gray-600">{{ $rule }}</span>
                </li>
            @endforeach
        </ol>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl px-4 py-3">{{ session('error') }}</div>
    @endif

    {{-- Registration status + exam CTA --}}
    @php
        $today = now()->toDateString();
        $notStarted = $competition->start_date && $competition->start_date->toDateString() > $today;
        $ended      = $competition->end_date && $competition->end_date->toDateString() < $today;
    @endphp
    @if($myRegistration)
        <div class="bg-stu-light border border-stu/30 rounded-2xl p-4 text-center">
            <p class="text-stu-dark font-bold text-sm">✓ You are registered</p>
            @if($competition->start_date)<p class="text-gray-500 text-xs mt-1">Competition date: {{ $competition->start_date->format('d M Y') }}</p>@endif
        </div>

        @if($myAttempts->isNotEmpty())
            <a href="{{ route('external.competitions.result', $competition) }}" class="block w-full py-3.5 bg-stu text-white rounded-2xl text-sm font-bold text-center">View My Result</a>
        @elseif($notStarted)
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center">
                <p class="text-sm text-fran font-medium">This competition starts on {{ $competition->start_date->format('d M Y') }}. The exam will open then.</p>
            </div>
        @elseif($ended)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
                <p class="text-sm text-amber-700 font-medium">This competition has ended.</p>
            </div>
        @elseif($paper)
            <form method="POST" action="{{ route('external.competitions.start', $competition) }}">
                @csrf
                <button type="submit" class="w-full py-3.5 bg-fran text-white rounded-2xl text-sm font-bold">I am Ready — Start Exam</button>
            </form>
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
                <p class="text-sm text-amber-700 font-medium">The question paper is not available yet.</p>
            </div>
        @endif
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
            <p class="text-amber-700 text-sm font-medium">Not registered</p>
            <p class="text-amber-600 text-xs mt-1">Contact your branch to register for this competition.</p>
        </div>
    @endif

    <a href="{{ route('external.practice.index') }}" class="block w-full py-3.5 border border-fran text-fran rounded-2xl text-sm font-bold text-center">Competition Practice</a>
    <a href="{{ route('external.competitions.index') }}" class="block w-full py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold text-center">Go Back</a>

</div>
@endsection
