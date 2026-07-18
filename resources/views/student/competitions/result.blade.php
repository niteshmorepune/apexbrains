@extends('layouts.student')
@section('title', 'Competition Result')

@section('content')
<div class="px-4 pt-6 pb-4 space-y-4">

    @if($attempt && $competition->results_declared_at)
        @php
            $passMark   = $attempt->paper?->pass_percentage ?? 75;
            $passed     = $attempt->percentage >= $passMark;
            $pct        = (int) round($attempt->percentage);
            $ringColor  = $passed ? '#2ECC71' : '#EF4444';
            $durationSec = $attempt->started_at && $attempt->submitted_at ? $attempt->submitted_at->diffInSeconds($attempt->started_at, true) : 0;
            $timeMins   = $durationSec > 0 ? floor($durationSec/60).':'.str_pad($durationSec%60, 2, '0', STR_PAD_LEFT) : '—';
            $totalQ     = $attempt->paper?->total_questions ?? 0;
            $wrong      = max(0, $totalQ - (int) $attempt->score);
            $circ       = 2 * 3.14159 * 52;
        @endphp

        {{-- Score ring --}}
        <div class="flex flex-col items-center">
            <div class="relative w-36 h-36">
                <svg class="w-36 h-36 -rotate-90" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="52" fill="none" stroke="#EDF0F5" stroke-width="10"/>
                    <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $ringColor }}" stroke-width="10" stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ - ($circ * $pct / 100) }}"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <p class="text-3xl font-black text-gray-900">{{ $pct }}%</p>
                    <p class="text-xs text-gray-400">Score</p>
                </div>
            </div>
            <p class="text-base font-bold text-gray-800 mt-3">{{ $competition->title }}</p>
            <span class="inline-block font-bold text-xs px-4 py-1 rounded-full mt-1.5 {{ $passed ? 'bg-stu text-white' : 'bg-red-500 text-white' }}">{{ $passed ? 'PASSED' : 'COMPLETED' }}</span>
        </div>

        {{-- Stats 2x2 --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-stu text-xl">✅</span><div><p class="text-lg font-black text-stu leading-none">{{ (int) $attempt->score }}/{{ $totalQ }}</p><p class="text-xs text-gray-400 mt-1">Correct</p></div></div>
            <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-red-500 text-xl">❌</span><div><p class="text-lg font-black text-red-500 leading-none">{{ $wrong }}/{{ $totalQ }}</p><p class="text-xs text-gray-400 mt-1">Wrong</p></div></div>
            <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-logo-amber text-xl">🎯</span><div><p class="text-lg font-black text-logo-amber leading-none">{{ $passMark }}%</p><p class="text-xs text-gray-400 mt-1">Pass Mark</p></div></div>
            <div class="bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-red-500 text-xl">⏱️</span><div><p class="text-lg font-black text-gray-700 leading-none">{{ $timeMins }}</p><p class="text-xs text-gray-400 mt-1">Time Taken</p></div></div>
            @if($rank)
                <div class="col-span-2 bg-white rounded-2xl border border-border p-4 flex items-center gap-3"><span class="text-fran text-xl">🏆</span><div><p class="text-lg font-black text-fran leading-none">#{{ $rank }}</p><p class="text-xs text-gray-400 mt-1">Your Rank</p></div></div>
            @endif
        </div>
    @elseif($attempt)
        <div class="bg-white rounded-2xl border border-border p-10 text-center">
            <div class="text-4xl mb-3">✅</div>
            <p class="font-bold text-gray-800">Submitted successfully</p>
            <p class="text-sm text-gray-500 mt-2">Your competition has been submitted successfully. Results will be available once they are declared by the administrator.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
            <div class="text-4xl mb-3">📝</div>
            <p class="font-medium text-gray-600">No result yet</p>
            <p class="text-sm mt-1">Complete the competition exam to see your result.</p>
        </div>
    @endif

    @if($certificate)
        <div class="bg-stu-light border border-stu/30 rounded-2xl p-4 flex items-center gap-3">
            <span class="text-2xl">🏅</span>
            <div class="flex-1">
                <p class="text-sm font-bold text-gray-800">Participation Certificate Ready</p>
                <p class="text-xs text-gray-500">{{ $certificate->certificate_number }}</p>
            </div>
        </div>
        <a href="{{ route('student.certificates.pdf', $certificate) }}" class="block w-full py-3.5 bg-stu text-white rounded-2xl text-sm font-bold text-center">Download Certificate</a>
    @endif

    <a href="{{ route('student.home') }}" class="block w-full py-3.5 bg-fran text-white rounded-2xl text-sm font-bold text-center">Back to Home</a>
    <a href="{{ route('student.competitions.index') }}" class="block w-full py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold text-center">All Competitions</a>

</div>
@endsection
