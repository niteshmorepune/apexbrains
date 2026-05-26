@extends('layouts.student')
@section('title', 'Practice')

@section('content')
<div class="p-4 space-y-4">

    {{-- Start Practice --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-sm font-bold text-gray-700 mb-4">Start a Practice Session</p>

        @error('level_id')
            <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('student.practice.start') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Level</label>
                <select name="level_id" required
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                    <option value="">Select a level</option>
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}"
                            @selected($student?->current_level_id === $level->id)>
                            Level {{ $level->number }}@if($level->title) — {{ $level->title }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Difficulty</label>
                    <select name="difficulty" required
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Questions</label>
                    <select name="count" required
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                    </select>
                </div>
            </div>
            <button type="submit"
                    class="w-full py-3 bg-stu text-white rounded-xl text-sm font-semibold hover:bg-stu-dark">
                Start Practice ⚡
            </button>
        </form>
    </div>

    {{-- Past Sessions --}}
    @if($pastSessions->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border">
                <p class="text-sm font-semibold text-gray-700">Recent Sessions</p>
            </div>
            <div class="divide-y divide-border">
                @foreach($pastSessions as $ps)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-stu/10 flex items-center justify-center flex-shrink-0">
                            <span class="text-stu font-bold text-sm">{{ number_format($ps->accuracy, 0) }}%</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">
                                Level {{ $ps->level?->number }}
                                <span class="text-gray-400 font-normal capitalize">· {{ $ps->difficulty }}</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $ps->questions_correct }}/{{ $ps->total_questions }} correct
                                · {{ $ps->completed_at?->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('student.practice.results', $ps) }}"
                           class="text-xs text-gray-400 hover:text-gray-600">View</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
