@extends('layouts.franchise')
@section('title', 'Promotion Review')
@section('page-title', 'Promotion Review')

@section('content')

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-fran">Eligible for Promotion</h2>
            <p class="text-xs text-gray-400 mt-0.5">Students who have passed their current level exam</p>
        </div>
        <span class="text-sm font-bold text-fran bg-blue-50 px-3 py-1 rounded-full">
            {{ $eligible->count() }} Eligible
        </span>
    </div>

    @if($eligible->count())
        <div class="divide-y divide-border">
            @foreach($eligible as $student)
                <div class="px-5 py-4 hover:bg-bg-light flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-fran flex items-center justify-center text-white font-bold flex-shrink-0">
                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800">{{ $student->full_name }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-fran">Currently: Level {{ $student->currentLevel->number }}</span>
                            <span class="text-xs text-gray-400">→</span>
                            <span class="text-xs text-stu font-medium">Level {{ $student->currentLevel->number + 1 }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('franchise.promotions.promote', $student) }}"
                          onsubmit="return confirm('Promote {{ addslashes($student->full_name) }} to Level {{ $student->currentLevel->number + 1 }}?')">
                        @csrf
                        @php
                            $nextLevel = $levels->firstWhere('number', $student->currentLevel->number + 1);
                        @endphp
                        <input type="hidden" name="new_level_id" value="{{ $nextLevel?->id }}">
                        <button type="submit"
                                class="px-4 py-2 bg-stu text-white rounded-xl text-sm font-medium hover:bg-green-600 transition-colors"
                                {{ !$nextLevel ? 'disabled' : '' }}>
                            Promote ↑
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        {{-- Batch promote --}}
        <div class="px-5 py-4 border-t border-border bg-bg-light">
            <p class="text-xs text-gray-500 mb-3">Batch promote all eligible students to their next level:</p>
            <form method="POST" action="{{ route('franchise.promotions.promote', 0) }}"
                  onsubmit="return confirm('Batch promote all {{ $eligible->count() }} eligible students?')">
                @csrf
                <input type="hidden" name="batch" value="1">
                <button type="submit"
                        class="px-5 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                    Batch Promote All ({{ $eligible->count() }})
                </button>
            </form>
        </div>
    @else
        <div class="px-5 py-12 text-center text-gray-400">
            <p class="text-base font-medium text-gray-500 mb-1">No students eligible for promotion</p>
            <p class="text-sm">Students need to pass their current level exam to appear here.</p>
        </div>
    @endif
</div>

@endsection
