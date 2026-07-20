@extends('layouts.franchise')
@section('title', $session->title)
@section('page-title', $session->title)

@section('page-actions')
    @if($session->status !== 'ended')
        <a href="{{ route('franchise.class-practice.project', $session) }}"
           class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">
            Launch Projector
        </a>
    @else
        <a href="{{ route('franchise.class-practice.results', $session) }}"
           class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">
            View Results
        </a>
    @endif
    <a href="{{ route('franchise.class-practice.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Back
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

    {{-- Session Info --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Session Details</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Code</span>
                    <span class="font-mono font-bold text-fran">{{ $session->session_code }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Level</span>
                    <span class="font-medium">{{ $session->level?->title ?? '—' }}</span>
                </div>
                @if($session->batch)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Batch</span>
                        <span class="font-medium">{{ $session->batch->name }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Category</span>
                    <span class="font-medium">{{ $session->category?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Type</span>
                    <span class="font-medium">{{ $session->type?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Questions</span>
                    <span class="font-medium">{{ $session->total_questions }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Timer</span>
                    <span class="font-medium">{{ $session->time_per_question_seconds }}s each</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Status</span>
                    @if($session->status === 'pending')
                        <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full">Pending</span>
                    @elseif($session->status === 'active')
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Live</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Ended</span>
                    @endif
                </div>
                @if($session->started_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Started</span>
                        <span class="text-gray-700">{{ $session->started_at->format('d M, H:i') }}</span>
                    </div>
                @endif
                @if($session->ended_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Ended</span>
                        <span class="text-gray-700">{{ $session->ended_at->format('d M, H:i') }}</span>
                    </div>
                @endif
            </div>

            @if($session->status !== 'ended')
                <div class="mt-5 pt-4 border-t border-border">
                    <a href="{{ route('franchise.class-practice.project', $session) }}"
                       class="block w-full text-center py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                        Launch Projector View
                    </a>
                </div>
            @endif

            <div class="mt-3 pt-3 border-t border-border">
                <form method="POST" action="{{ route('franchise.class-practice.destroy', $session) }}"
                      onsubmit="return confirm('Delete this class practice session? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="block w-full text-center py-2.5 border border-red-200 text-red-500 rounded-xl text-sm font-semibold hover:bg-red-50">
                        Delete Session
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Question List --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-fran">
                Questions ({{ $session->sessionQuestions->count() }})
            </h2>
        </div>
        <div class="divide-y divide-border">
            @foreach($session->sessionQuestions as $sq)
                <div class="px-5 py-4 @if($session->current_question_index === $sq->sort_order && $session->status === 'active') bg-blue-50 @endif">
                    <div class="flex items-start gap-3">
                        <span class="text-xs font-bold text-gray-400 w-6 flex-shrink-0 mt-0.5">
                            {{ $sq->sort_order }}.
                            @if($session->current_question_index === $sq->sort_order && $session->status === 'active')
                                <span class="block w-1.5 h-1.5 bg-green-500 rounded-full mt-1 mx-auto"></span>
                            @endif
                        </span>
                        <div class="flex-1">
                            <p class="text-sm text-gray-800">{{ $sq->question->question_text }}</p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 mt-2">
                                @foreach(['a', 'b', 'c', 'd'] as $opt)
                                    @php $val = $sq->question->{'option_' . $opt}; @endphp
                                    @if($val)
                                        <p class="text-xs @if(strtolower($sq->question->correct_answer) === $opt) text-green-600 font-medium @else text-gray-500 @endif">
                                            {{ strtoupper($opt) }}) {{ $val }}
                                        </p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
