@extends('layouts.student')
@section('title', 'Competition Practice')

@section('content')
<x-student-header title="Competition Practice" :back="route('student.competitions.index')" />
<div class="px-4 pb-4 space-y-4">

    <div class="bg-fran-light rounded-2xl p-4">
        <p class="text-sm font-bold text-fran">Competition Practice</p>
        <p class="text-xs text-gray-500 mt-0.5">Auto-generated for your level, matching real competition format.</p>
    </div>

    @if(session('error'))
        <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ session('error') }}</div>
    @endif

    @if($totalQuestions > 0)
        <div class="bg-white rounded-2xl border border-border p-5 text-center">
            <p class="text-3xl font-black text-admin">{{ $totalQuestions }}</p>
            <p class="text-xs text-gray-500 mt-1">Questions · {{ $durationMinutes }} min countdown</p>

            <form method="POST" action="{{ route('student.competitions.practice.start') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full py-3 bg-fran text-white rounded-2xl text-sm font-semibold">
                    Start Practice →
                </button>
            </form>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
            <p class="text-sm">No practice configuration is set up for your level yet. Please contact your branch.</p>
        </div>
    @endif

    @if($pastAttempts->isNotEmpty())
        <div>
            <p class="text-sm font-bold text-gray-800 mb-2">Recent Attempts</p>
            <div class="space-y-2.5">
                @foreach($pastAttempts as $att)
                    <a href="{{ route('student.competitions.practice.result', $att) }}"
                       class="bg-white rounded-2xl border border-border p-3.5 flex items-center gap-3">
                        <span class="text-fran">📅</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">{{ $att->submitted_at?->format('d M, g:i A') }}</p>
                            <p class="text-xs text-gray-400">{{ $att->level?->title }} · {{ $att->score }} correct</p>
                        </div>
                        <span class="text-base font-black {{ $att->percentage >= 75 ? 'text-stu' : 'text-logo-amber' }}">
                            {{ number_format($att->percentage, 0) }}%
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
