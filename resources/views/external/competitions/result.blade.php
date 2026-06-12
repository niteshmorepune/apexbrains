@extends('layouts.external')
@section('title', 'Competition Result')

@section('content')
<x-student-header :title="$competition->title" :back="route('external.competitions.index')" />

<div class="px-4 pb-4 space-y-4">

    @if($attempt)
        {{-- Score ring --}}
        <div class="bg-white rounded-2xl border border-border p-6 text-center">
            <div class="w-28 h-28 mx-auto rounded-full flex items-center justify-center"
                 style="background: conic-gradient(#1A73E8 {{ (float) $attempt->percentage * 3.6 }}deg, #E5EAF1 0deg);">
                <div class="w-20 h-20 rounded-full bg-white flex flex-col items-center justify-center">
                    <span class="text-2xl font-black text-fran">{{ number_format($attempt->percentage ?? 0, 0) }}%</span>
                    <span class="text-[10px] text-gray-400">Score</span>
                </div>
            </div>
            <p class="mt-3 font-bold text-gray-800">{{ $competition->title }}</p>
            <p class="text-xs text-gray-400">Submitted {{ $attempt->submitted_at?->format('d M Y, g:i A') }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-2xl border border-border p-3 text-center">
                <p class="text-xl font-black text-stu">{{ $attempt->score ?? 0 }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Correct</p>
            </div>
            <div class="bg-white rounded-2xl border border-border p-3 text-center">
                <p class="text-xl font-black text-gray-800">{{ $attempt->paper?->total_questions ?? '—' }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Questions</p>
            </div>
        </div>

        @if($certificate)
            <a href="{{ route('external.certificates.show', $certificate) }}"
               class="block text-center py-3.5 bg-fran text-white rounded-2xl text-sm font-bold">🎓 View Participation Certificate</a>
        @endif
    @else
        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
            <div class="text-4xl mb-2">🏆</div>
            <p class="font-medium text-gray-600">No attempt found</p>
        </div>
    @endif

    <a href="{{ route('external.competitions.index') }}" class="block text-center py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold">Back to Competitions</a>

</div>
@endsection
