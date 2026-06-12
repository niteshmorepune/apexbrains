@extends('layouts.external')
@section('title', 'Practice')

@section('content')
<div>
    <x-student-header title="Competition Practice" :back="route('external.home')" />

    <div class="px-4 pb-4 space-y-5">
        @error('difficulty')
            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ $message }}</div>
        @enderror

        <div class="bg-fran-light rounded-2xl p-4">
            <p class="text-sm font-bold text-fran">Practice from the Question Bank</p>
            <p class="text-xs text-gray-500 mt-0.5">Each session pulls a fresh random set of questions. Pick a difficulty to begin.</p>
        </div>

        {{-- Pick a difficulty — tapping it starts the session immediately --}}
        <form method="POST" action="{{ route('external.practice.start') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="count" value="20">

            @foreach([
                ['value' => 'easy',   'emoji' => '🟢', 'title' => 'Easy',   'sub' => 'Build confidence with simpler sums',    'bg' => 'bg-stu-light'],
                ['value' => 'medium', 'emoji' => '🟡', 'title' => 'Medium', 'sub' => 'Sharpen your speed and accuracy',        'bg' => 'bg-amber-50'],
                ['value' => 'hard',   'emoji' => '🔴', 'title' => 'Hard',   'sub' => 'Challenge yourself with tougher drills', 'bg' => 'bg-red-50'],
                ['value' => 'all',    'emoji' => '🎯', 'title' => 'Mixed',  'sub' => 'A blend of all difficulties',            'bg' => 'bg-fran-light'],
            ] as $diff)
                <button type="submit" name="difficulty" value="{{ $diff['value'] }}"
                        class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl {{ $diff['bg'] }} flex items-center justify-center text-2xl flex-shrink-0">{{ $diff['emoji'] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-gray-800">{{ $diff['title'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">{{ $diff['sub'] }}</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            @endforeach
        </form>

        {{-- Recent Sessions --}}
        @if($pastSessions->isNotEmpty())
            <div>
                <p class="text-sm font-bold text-gray-800 mb-2">Recent Sessions</p>
                <div class="space-y-2.5">
                    @foreach($pastSessions as $ps)
                        <div class="bg-white rounded-2xl border border-border p-3.5 flex items-center gap-3">
                            <span class="text-fran">📅</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">{{ $ps->completed_at?->format('d M, g:i A') ?? 'In progress' }}</p>
                                <p class="text-xs text-gray-400">{{ $ps->questions_correct ?? 0 }}/{{ $ps->total_questions }} correct · {{ number_format($ps->accuracy ?? 0, 0) }}% accuracy</p>
                            </div>
                            <span class="text-base font-black text-fran">{{ number_format($ps->accuracy ?? 0, 0) }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
