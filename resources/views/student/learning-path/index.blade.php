@extends('layouts.student')
@section('title', 'My Learning Path')

@section('subheader')
    <p class="text-white/80 text-xs">
        @if($student?->currentLevel)
            Currently on Level {{ $student->currentLevel->number }}
            @if($student->currentLevel->title) — {{ $student->currentLevel->title }}@endif
        @else
            Start your journey
        @endif
    </p>
@endsection

@section('content')
<div class="p-4 space-y-2">

    @foreach($levels as $level)
        @php
            $isCompleted = in_array($level->id, $completedLevelIds);
            $isCurrent   = $student?->current_level_id === $level->id;
            $isLocked    = !$isCompleted && !$isCurrent && $level->number > $currentLevelNumber;
        @endphp

        <a href="{{ $isLocked ? '#' : route('student.levels.show', $level) }}"
           class="block bg-white rounded-2xl border p-4 transition-all
               {{ $isCurrent ? 'border-stu shadow-sm' : 'border-border' }}
               {{ $isLocked ? 'opacity-50 cursor-not-allowed' : 'hover:border-stu' }}">
            <div class="flex items-center gap-4">
                {{-- Level number circle --}}
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-lg flex-shrink-0
                    {{ $isCompleted ? 'bg-stu text-white' : ($isCurrent ? 'bg-stu/20 text-stu' : 'bg-bg-mid text-gray-400') }}">
                    @if($isCompleted)
                        ✓
                    @else
                        {{ $level->number }}
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-gray-800 text-sm">Level {{ $level->number }}</p>
                        @if($isCurrent)
                            <span class="text-xs bg-stu text-white px-2 py-0.5 rounded-full">Current</span>
                        @elseif($isCompleted)
                            <span class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full">Completed</span>
                        @elseif($isLocked)
                            <span class="text-xs bg-gray-100 text-gray-400 px-2 py-0.5 rounded-full">Locked</span>
                        @endif
                    </div>
                    @if($level->title)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $level->title }}</p>
                    @endif
                    @if($level->learning_objectives && count($level->learning_objectives) > 0)
                        <p class="text-xs text-gray-400 mt-1">{{ count($level->learning_objectives) }} objectives</p>
                    @endif
                </div>

                @if(!$isLocked)
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @else
                    <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                @endif
            </div>
        </a>
    @endforeach

</div>
@endsection
