@extends('layouts.student')
@section('title', 'My Learning')

@section('content')
@php
    $levelColors = [
        1=>'#87CEEB', 2=>'#2ECC71', 3=>'#00BCD4', 4=>'#FFD54F', 5=>'#F5A623',
        6=>'#FF69B4', 7=>'#D42B2B', 8=>'#9C27B0', 9=>'#1A73E8', 10=>'#00897B',
        11=>'#FF6F00', 12=>'#AD1457', 13=>'#283593', 14=>'#212121',
    ];
    $totalLevels = $levels->count();
    $overallPct  = $totalLevels > 0 ? round(count($completedLevelIds) / $totalLevels * 100) : 0;
@endphp

<x-student-header title="My Learning" :back="route('student.home')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Overall progress --}}
    @if($student?->currentLevel)
        <div class="bg-white rounded-2xl border border-border p-4">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-bold text-gray-800">Overall Progress</p>
                <p class="text-sm font-bold text-fran">L{{ $student->currentLevel->number }} / L{{ $totalLevels }}</p>
            </div>
            <div class="h-2 bg-bg-mid rounded-full overflow-hidden mb-1.5">
                <div class="h-full bg-fran rounded-full" style="width: {{ max(3, $overallPct) }}%"></div>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-400">
                <span>Level {{ $student->currentLevel->number }} of {{ $totalLevels }} total levels</span>
                <span>{{ $overallPct }}% complete</span>
            </div>
        </div>
    @endif

    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Your Level Journey</p>

    {{-- Connected level journey --}}
    <div class="grid grid-cols-3 gap-x-2 gap-y-5">
        @foreach($levels as $level)
            @php
                $isCompleted = in_array($level->id, $completedLevelIds);
                $isCurrent   = $student?->current_level_id === $level->id;
                $isLocked    = !$isCompleted && !$isCurrent && $level->number > $currentLevelNumber;
                $color       = $levelColors[$level->number] ?? '#2ECC71';
            @endphp
            <a href="{{ $isLocked ? '#' : route('student.levels.show', $level) }}"
               class="flex flex-col items-center text-center {{ $isLocked ? 'pointer-events-none' : '' }}">
                <div class="w-16 h-16 rounded-full flex items-center justify-center font-black text-lg shadow-sm
                            {{ $isCurrent ? 'ring-4 ring-offset-2' : '' }}"
                     style="{{ $isLocked ? 'background:#E5E9F0;color:#A8B0BE' : 'background:'.$color.';color:#fff' }}{{ $isCurrent ? ';--tw-ring-color:'.$color : '' }}">
                    @if($isLocked) 🔒 @else L{{ $level->number }} @endif
                </div>
                <p class="text-[11px] font-semibold text-gray-700 mt-1.5 leading-tight">{{ \Illuminate\Support\Str::limit($level->title ?? ('Level '.$level->number), 14) }}</p>
                @if($isCompleted)
                    <span class="text-[10px] font-bold text-stu">✓ Cert</span>
                @elseif($isCurrent)
                    <span class="text-[10px] font-bold text-logo-amber">In Progress</span>
                @else
                    <span class="text-[10px] text-gray-300">{{ $isLocked ? 'Locked' : 'Start' }}</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Continue CTA --}}
    @if($student?->currentLevel)
        <a href="{{ route('student.levels.show', $student->currentLevel) }}"
           class="block bg-fran text-white text-center font-bold py-3.5 rounded-2xl text-sm">
            Continue Level {{ $student->currentLevel->number }}
        </a>
    @endif

</div>
@endsection
