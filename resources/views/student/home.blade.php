@extends('layouts.student')
@section('title', 'Home')

@section('content')
<div class="p-4 space-y-4">

    {{-- Greeting --}}
    <div class="bg-stu rounded-2xl p-5 text-white">
        <p class="text-white/70 text-sm">
            @php
                $hour = now()->hour;
                echo $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            @endphp
        </p>
        <p class="text-xl font-bold mt-0.5">{{ auth()->user()->name }}</p>
        @if($student?->currentLevel)
            <div class="mt-3 flex items-center gap-2">
                <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">
                    Level {{ $student->currentLevel->number }}
                </span>
                @if($student->currentLevel->title)
                    <span class="text-white/70 text-xs">{{ $student->currentLevel->title }}</span>
                @endif
            </div>
        @endif
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl border border-border p-4 text-center">
            <p class="text-2xl font-black text-stu">{{ $practiceThisWeek }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Practice this week</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-4 text-center">
            <p class="text-2xl font-black text-fran">{{ $recentAttempts->where('is_passed', true)->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Exams passed</p>
        </div>
    </div>

    {{-- Upcoming Exam --}}
    @if($upcomingExam)
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Upcoming Exam</p>
            <p class="font-semibold text-gray-800">{{ $upcomingExam->title }}</p>
            <div class="flex items-center justify-between mt-2">
                <p class="text-xs text-gray-500">{{ $upcomingExam->scheduled_at?->format('d M Y, H:i') }}</p>
                <a href="{{ route('student.exams.show', $upcomingExam) }}"
                   class="text-xs bg-fran text-white px-3 py-1.5 rounded-lg font-medium">
                    View →
                </a>
            </div>
        </div>
    @endif

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('student.practice.index') }}"
           class="bg-white rounded-2xl border border-border p-4 flex flex-col items-center gap-2 hover:border-stu transition-colors">
            <div class="w-10 h-10 bg-stu/10 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-stu" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-700">Practice</span>
        </a>
        <a href="{{ route('student.exams.index') }}"
           class="bg-white rounded-2xl border border-border p-4 flex flex-col items-center gap-2 hover:border-fran transition-colors">
            <div class="w-10 h-10 bg-fran/10 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-fran" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-700">Exams</span>
        </a>
        <a href="{{ route('student.learning-path') }}"
           class="bg-white rounded-2xl border border-border p-4 flex flex-col items-center gap-2 hover:border-stu transition-colors">
            <div class="w-10 h-10 bg-stu/10 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-stu" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-700">My Path</span>
        </a>
        <a href="{{ route('student.competitions.index') }}"
           class="bg-white rounded-2xl border border-border p-4 flex flex-col items-center gap-2 hover:border-yellow-400 transition-colors">
            <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21H5a2 2 0 01-2-2v-1a5 5 0 015-5h8a5 5 0 015 5v1a2 2 0 01-2 2h-3M12 3a4 4 0 100 8 4 4 0 000-8z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-700">Compete</span>
        </a>
    </div>

    {{-- Recent Exams --}}
    @if($recentAttempts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-4 py-3 border-b border-border">
                <p class="text-sm font-semibold text-gray-700">Recent Exams</p>
            </div>
            <div class="divide-y divide-border">
                @foreach($recentAttempts as $attempt)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                            <span class="text-xs font-bold">{{ $attempt->is_passed ? '✓' : '✗' }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $attempt->exam?->title }}</p>
                            <p class="text-xs text-gray-400">{{ $attempt->submitted_at?->format('d M') }}</p>
                        </div>
                        <span class="text-sm font-bold {{ $attempt->is_passed ? 'text-green-600' : 'text-red-500' }}">
                            {{ number_format($attempt->percentage, 0) }}%
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t border-border">
                <a href="{{ route('student.exams.index') }}" class="text-xs text-fran font-medium">View all exams →</a>
            </div>
        </div>
    @endif

</div>
@endsection
