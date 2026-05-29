@extends('layouts.student')
@section('title', $competition->title)

@section('content')
<div class="p-4 space-y-4">

    {{-- Competition header --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-start gap-3 mb-3">
            <div class="w-12 h-12 rounded-2xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                <span class="text-2xl">🏆</span>
            </div>
            <div class="flex-1">
                <p class="font-bold text-gray-800">{{ $competition->title }}</p>
                @if($competition->description)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $competition->description }}</p>
                @endif
            </div>
        </div>
        {{-- Info grid --}}
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="font-bold text-admin">{{ $competition->duration_minutes ?? 10 }} Min</p>
                <p class="text-xs text-gray-400">Duration</p>
            </div>
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="font-bold text-fran">{{ $competition->total_questions ?? 120 }} MCQ</p>
                <p class="text-xs text-gray-400">Questions</p>
            </div>
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="font-bold text-logo-amber">{{ $competition->pass_percentage ?? 75 }}%</p>
                <p class="text-xs text-gray-400">Pass Marks</p>
            </div>
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="font-bold text-admin">{{ $myAttempts->count() }} of 3</p>
                <p class="text-xs text-gray-400">Attempts</p>
            </div>
        </div>
    </div>

    {{-- Rules --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Rules and Instructions</p>
        <ol class="space-y-2 text-sm text-gray-600">
            @foreach([
                'Exam will open in fullscreen mode',
                'Do not switch browser tabs or windows',
                'Each question must be answered within the time limit',
                'Once submitted, answers cannot be changed',
                'Results will be available immediately after submission',
                'In case of technical issues, contact your branch',
            ] as $i => $rule)
                <li class="flex items-start gap-2">
                    <span class="w-5 h-5 rounded-full bg-fran-light text-fran text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">{{ $i + 1 }}</span>
                    <span>{{ $rule }}</span>
                </li>
            @endforeach
        </ol>
    </div>

    {{-- CTA --}}
    @if($registration)
        @if($myAttempts->isEmpty())
            <form method="POST" action="{{ route('student.competitions.start', $competition) }}">
                @csrf
                <button type="submit"
                        class="w-full py-4 bg-fran text-white rounded-2xl text-sm font-bold hover:bg-fran-dark transition-colors">
                    I am Ready — Start Exam
                </button>
            </form>
        @else
            <a href="{{ route('student.competitions.result', $competition) }}"
               class="block w-full py-4 bg-stu text-white rounded-2xl text-sm font-bold text-center hover:bg-stu-dark transition-colors">
                View My Result
            </a>
        @endif
        <p class="text-center text-xs text-gray-400">Exam will open in fullscreen mode</p>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center">
            <p class="text-sm text-yellow-700 font-medium">You are not registered for this competition</p>
            <p class="text-xs text-yellow-600 mt-1">Contact your branch to register</p>
        </div>
    @endif

    <a href="{{ route('student.competitions.index') }}"
       class="block text-center text-sm text-gray-400 hover:text-gray-600 py-2">
        ← Back to Competitions
    </a>

</div>
@endsection
