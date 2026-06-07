@extends('layouts.student')
@section('title', 'Results')

@section('content')
@php $borderColors = ['#D42B2B', '#F5A623', '#FFD54F', '#FF69B4', '#1A73E8', '#9C27B0']; @endphp

<x-student-header title="Results" :back="route('student.home')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Stats summary --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-xl font-black text-stu">{{ $totalExams }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Total Exams</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-xl font-black text-fran">{{ number_format($avgScore, 0) }}%</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Avg Score</p>
        </div>
        <div class="bg-white rounded-2xl border border-border p-3 text-center">
            <p class="text-xl font-black text-logo-amber">{{ $passed }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Passed</p>
        </div>
    </div>

    {{-- Past exams list --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Past Exams</p>
        <div class="space-y-3">
            @forelse($attempts as $i => $attempt)
                <a href="{{ route('student.exams.result', $attempt->exam) }}" class="block bg-white rounded-2xl border border-border overflow-hidden">
                    <div class="flex items-stretch">
                        <span class="w-1.5 flex-shrink-0" style="background-color: {{ $borderColors[$i % count($borderColors)] }}"></span>
                        <div class="flex items-center gap-3 p-4 flex-1 min-w-0">
                            <span class="w-10 h-10 rounded-full bg-bg-light flex items-center justify-center flex-shrink-0">📊</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">{{ $attempt->exam?->title ?? 'Exam' }}</p>
                                <p class="text-xs text-gray-400">{{ $attempt->submitted_at?->format('d M Y') }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $attempt->is_passed ? 'text-stu bg-stu-light' : 'text-red-500 bg-red-50' }}">{{ $attempt->is_passed ? 'Pass' : 'Fail' }} {{ number_format($attempt->percentage, 0) }}%</span>
                                <p class="text-xs text-fran font-semibold mt-1">View Report</p>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
                    <div class="text-4xl mb-2">📝</div>
                    <p class="font-medium text-gray-600">No results yet</p>
                    <p class="text-sm mt-1">Complete an exam to see your results here.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($attempts->hasPages())
        <div class="py-2">{{ $attempts->links('pagination::tailwind') }}</div>
    @endif

</div>
@endsection
