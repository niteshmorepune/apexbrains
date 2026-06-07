@extends('layouts.student')
@section('title', 'Practice Papers')

@section('content')
<x-student-header title="Practice Papers" :back="route('student.competitions.index')" />
<div class="px-4 pb-4 space-y-3">

    <div class="bg-fran-light rounded-2xl p-4 mb-1">
        <p class="text-sm font-bold text-fran">Competition Practice Papers</p>
        <p class="text-xs text-gray-500 mt-0.5">Practise with actual competition-style questions</p>
    </div>

    @forelse($papers as $paper)
        @php $attempted = in_array($paper->id, $attemptedPaperIds); @endphp
        <div class="bg-white rounded-2xl border border-border p-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-fran flex items-center justify-center text-white font-black flex-shrink-0">
                    {{ $paper->paper_number }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm">{{ $paper->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $paper->total_questions }}Q · {{ $paper->duration_minutes }}min
                        @if($paper->difficulty)
                            · <span class="capitalize">{{ $paper->difficulty }}</span>
                        @endif
                    </p>
                    @if($paper->description)
                        <p class="text-xs text-gray-400 mt-1 line-clamp-1">{{ $paper->description }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between">
                @if($attempted)
                    <span class="text-xs bg-green-50 text-green-600 px-2 py-1 rounded-full">Attempted</span>
                    <div class="flex gap-2">
                        <a href="{{ route('student.competitions.practice.result', $paper) }}"
                           class="text-xs text-gray-500 border border-border px-3 py-1.5 rounded-lg hover:bg-bg-light">
                            Results
                        </a>
                        <form method="POST" action="{{ route('student.competitions.practice.start', $paper) }}">
                            @csrf
                            <button type="submit" class="text-xs bg-fran text-white px-3 py-1.5 rounded-lg font-medium">
                                Retry
                            </button>
                        </form>
                    </div>
                @else
                    <span class="text-xs text-gray-400">Not attempted</span>
                    <form method="POST" action="{{ route('student.competitions.practice.start', $paper) }}">
                        @csrf
                        <button type="submit" class="text-xs bg-fran text-white px-4 py-1.5 rounded-lg font-medium">
                            Start →
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
            <p class="text-sm">No practice papers available yet.</p>
        </div>
    @endforelse

    <a href="{{ route('student.competitions.index') }}"
       class="block text-center text-sm text-gray-400 hover:text-gray-600 py-2">
        ← Back to Competitions
    </a>

</div>
@endsection
