@extends('layouts.franchise')
@section('title', $exam->title)
@section('page-title', $exam->title)

@section('page-actions')
    <a href="{{ route('franchise.exams.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Exams
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

    {{-- Exam info --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Exam Details</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Level</span>
                    <span class="font-medium">Level {{ $exam->level?->number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Questions</span>
                    <span class="font-medium">{{ $exam->total_questions }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Duration</span>
                    <span class="font-medium">{{ $exam->duration_minutes }} min</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Pass Mark</span>
                    <span class="font-medium text-fran">{{ number_format($exam->pass_percentage, 0) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Max Attempts</span>
                    <span class="font-medium">{{ $exam->max_attempts ?? 'Unlimited' }}</span>
                </div>
                @if($exam->scheduled_at)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Scheduled</span>
                        <span class="font-medium text-fran">{{ $exam->scheduled_at_ist->format('d M Y, H:i') }}</span>
                    </div>
                @endif
                @if($exam->expires_at)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Expires</span>
                        <span class="font-medium">{{ $exam->expires_at_ist->format('d M Y') }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    @if($exam->is_active)
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-white rounded-xl border border-border p-3 text-center">
                <p class="text-xl font-black text-gray-800">{{ $attemptCount }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Attempts</p>
            </div>
            <div class="bg-white rounded-xl border border-border p-3 text-center">
                <p class="text-xl font-black text-green-600">{{ $passCount }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Passed</p>
            </div>
            <div class="bg-white rounded-xl border border-border p-3 text-center">
                <p class="text-xl font-black text-fran">{{ $avgScore ? number_format($avgScore, 0) . '%' : '—' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Avg Score</p>
            </div>
        </div>
    </div>

    {{-- Recent Attempts --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-fran">Recent Attempts</h2>
        </div>
        <div class="divide-y divide-border">
            @forelse($recentAttempts as $attempt)
                <div class="px-5 py-4 flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                        {{ $attempt->is_passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                        <span class="text-xs font-bold">{{ $attempt->is_passed ? '✓' : '✗' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800">{{ $attempt->student?->full_name }}</p>
                        <p class="text-xs text-gray-400">
                            Attempt #{{ $attempt->attempt_number }}
                            · {{ $attempt->submitted_at?->format('d M Y, H:i') }}
                            @if($attempt->tab_switch_count > 0)
                                · <span class="text-amber-500">{{ $attempt->tab_switch_count }} violation{{ $attempt->tab_switch_count > 1 ? 's' : '' }}</span>
                            @endif
                        </p>
                    </div>
                    <span class="font-bold text-sm {{ $attempt->is_passed ? 'text-green-600' : 'text-red-500' }}">
                        {{ number_format($attempt->percentage, 0) }}%
                    </span>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-gray-400">
                    No attempts yet. Share this exam with your students.
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
