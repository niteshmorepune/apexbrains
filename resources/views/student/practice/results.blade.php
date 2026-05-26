@extends('layouts.student')
@section('title', 'Practice Results')

@section('content')
<div class="p-4 space-y-4">

    {{-- Score card --}}
    <div class="bg-stu rounded-2xl p-6 text-white text-center">
        <p class="text-white/70 text-sm mb-1">Your Score</p>
        <p class="text-5xl font-black mb-1">{{ number_format($session->accuracy, 0) }}%</p>
        <p class="text-white/70 text-sm">
            {{ $session->questions_correct }} of {{ $session->total_questions }} correct
        </p>
        <div class="mt-4 flex items-center justify-center gap-4 text-sm">
            <div class="text-center">
                <p class="font-bold">Level {{ $session->level?->number }}</p>
                <p class="text-white/60 text-xs">Level</p>
            </div>
            <div class="w-px h-8 bg-white/20"></div>
            <div class="text-center">
                <p class="font-bold capitalize">{{ $session->difficulty }}</p>
                <p class="text-white/60 text-xs">Difficulty</p>
            </div>
            <div class="w-px h-8 bg-white/20"></div>
            <div class="text-center">
                <p class="font-bold">{{ $session->completed_at?->format('d M') }}</p>
                <p class="text-white/60 text-xs">Date</p>
            </div>
        </div>
    </div>

    {{-- Performance bar --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Performance</p>
        <div class="flex items-center gap-3 mb-2">
            <div class="flex-1 h-3 bg-bg-mid rounded-full overflow-hidden">
                <div class="h-full bg-stu rounded-full"
                     style="width: {{ number_format($session->accuracy, 0) }}%"></div>
            </div>
            <span class="text-sm font-bold text-stu w-10 text-right">{{ number_format($session->accuracy, 0) }}%</span>
        </div>
        <div class="grid grid-cols-3 gap-3 mt-4 text-center">
            <div class="bg-green-50 rounded-xl p-3">
                <p class="text-xl font-black text-green-600">{{ $session->questions_correct }}</p>
                <p class="text-xs text-green-600 mt-0.5">Correct</p>
            </div>
            <div class="bg-red-50 rounded-xl p-3">
                <p class="text-xl font-black text-red-500">{{ $session->total_questions - $session->questions_correct }}</p>
                <p class="text-xs text-red-500 mt-0.5">Wrong</p>
            </div>
            <div class="bg-bg-mid rounded-xl p-3">
                <p class="text-xl font-black text-gray-600">{{ $session->total_questions }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total</p>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-3">
        <a href="{{ route('student.practice.index') }}"
           class="block w-full py-3 bg-stu text-white rounded-2xl text-sm font-semibold text-center hover:bg-stu-dark">
            Practice Again ⚡
        </a>
        <a href="{{ route('student.home') }}"
           class="block w-full py-3 border border-border text-gray-600 rounded-2xl text-sm font-semibold text-center hover:bg-bg-light">
            Back to Home
        </a>
    </div>

</div>
@endsection
