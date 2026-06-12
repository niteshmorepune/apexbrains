@extends('layouts.external')
@section('title', 'Practice Result')

@section('content')
<x-student-header title="Practice Result" :back="route('external.practice.index')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Score ring --}}
    <div class="bg-white rounded-2xl border border-border p-6 text-center">
        <div class="w-28 h-28 mx-auto rounded-full flex items-center justify-center"
             style="background: conic-gradient(#1A73E8 {{ (float) $session->accuracy * 3.6 }}deg, #E5EAF1 0deg);">
            <div class="w-20 h-20 rounded-full bg-white flex flex-col items-center justify-center">
                <span class="text-2xl font-black text-fran">{{ number_format($session->accuracy ?? 0, 0) }}%</span>
                <span class="text-[10px] text-gray-400">Accuracy</span>
            </div>
        </div>
        <p class="mt-3 font-bold text-gray-800">{{ ucfirst($session->difficulty ?? 'Mixed') }} Practice</p>
        <p class="text-xs text-gray-400">{{ $session->completed_at?->format('d M Y, g:i A') }}</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-xl font-black text-stu">{{ $session->questions_correct ?? 0 }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Correct</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-xl font-black text-gray-800">{{ $session->total_questions }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Questions</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-xl font-black text-logo-amber">{{ $avgSpeed !== null ? $avgSpeed.'s' : '—' }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Avg / Q</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 pt-1">
        <a href="{{ route('external.practice.index') }}" class="block text-center py-3.5 bg-fran text-white rounded-2xl text-sm font-bold">Practice Again</a>
        <a href="{{ route('external.results') }}" class="block text-center py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold">All Results</a>
    </div>

</div>
@endsection
