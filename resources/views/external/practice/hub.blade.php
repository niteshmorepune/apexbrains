@extends('layouts.external')
@section('title', 'Practice Hub')

@section('content')
<div class="p-4 space-y-4">

    <div>
        <h1 class="text-lg font-bold text-admin">Practice Hub</h1>
        <p class="text-xs text-gray-400 mt-0.5">Advanced mental math and speed drills</p>
    </div>

    {{-- Progress --}}
    <div class="bg-fran rounded-2xl p-5 text-white">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-semibold">Papers Completed</p>
            <p class="text-sm font-bold">{{ $pct }}%</p>
        </div>
        <p class="text-white/70 text-xs mb-2">{{ $doneCount }}/{{ $totalPapers }} Papers Completed</p>
        <div class="h-2 bg-white/20 rounded-full overflow-hidden">
            <div class="h-full bg-white rounded-full transition-all" style="width: {{ max(3, $pct) }}%"></div>
        </div>
    </div>

    {{-- Milestone checklist --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Milestones</p>
        <ul class="space-y-3">
            @foreach($milestones as $m)
                <li class="flex items-center gap-3">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs flex-shrink-0
                        {{ $m['done'] ? 'bg-stu text-white' : 'border-2 border-border text-transparent' }}">
                        {{ $m['done'] ? '✓' : '○' }}
                    </span>
                    <span class="text-sm {{ $m['done'] ? 'text-gray-700' : 'text-gray-400' }}">{{ $m['label'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Continue CTA --}}
    <a href="{{ route('external.practice.index') }}"
       class="block bg-fran text-white text-center font-bold py-3.5 rounded-2xl text-sm hover:bg-fran-dark transition-colors">
        Continue Practice
    </a>

</div>
@endsection
