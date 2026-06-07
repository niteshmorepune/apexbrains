@extends('layouts.external')
@section('title', 'Practice Hub')

@section('content')
<x-student-header title="Practice Hub" :back="route('external.home')" subtitle="Advanced mental math and speed drills" />

<div class="px-4 pb-4 space-y-4">

    {{-- Papers completed --}}
    <div class="bg-white rounded-2xl border border-border p-4">
        <div class="flex items-center justify-between mb-1">
            <p class="text-sm font-bold text-gray-800">Papers Completed</p>
            <span class="text-sm font-bold text-fran">{{ $pct }}%</span>
        </div>
        <div class="h-2 bg-bg-mid rounded-full overflow-hidden mb-1.5">
            <div class="h-full bg-fran rounded-full" style="width: {{ max(3, $pct) }}%"></div>
        </div>
        <p class="text-xs text-gray-400">{{ $doneCount }}/{{ $totalPapers }} Papers Completed</p>
    </div>

    {{-- Topic Checklist (milestones) --}}
    <div class="bg-white rounded-2xl border border-border p-4">
        <p class="text-sm font-bold text-gray-800 mb-3">Topic Checklist</p>
        <ul class="space-y-2.5">
            @foreach($milestones as $m)
                <li class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ $m['done'] ? 'bg-stu-light' : 'bg-bg-light' }}">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold flex-shrink-0
                        {{ $m['done'] ? 'bg-stu text-white' : 'border-2 border-gray-300 text-transparent' }}">{{ $m['done'] ? '✓' : '' }}</span>
                    <span class="text-sm {{ $m['done'] ? 'text-gray-700' : 'text-gray-400' }}">{{ $m['label'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <a href="{{ route('external.practice.index') }}" class="block bg-fran text-white text-center font-bold py-3.5 rounded-2xl text-sm">Continue Practice</a>

</div>
@endsection
