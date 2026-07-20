@extends('layouts.admin')
@section('title', $exam->title)
@section('page-title', $exam->title)

@section('page-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.exams.edit', $exam) }}"
           class="px-4 py-2 bg-fran text-white text-sm font-semibold rounded-xl hover:bg-fran-dark transition-colors">Edit</a>
        <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}"
              onsubmit="return confirm('Delete exam “{{ $exam->title }}”? This also removes all student attempts and answers for it. This cannot be undone.');">
            @csrf @method('DELETE')
            <button type="submit"
                    class="px-4 py-2 border border-red-300 text-red-500 text-sm font-semibold rounded-xl hover:bg-red-50 transition-colors">Delete</button>
        </form>
        <a href="{{ route('admin.exams.index') }}"
           class="px-4 py-2 border border-border text-gray-600 text-sm font-semibold rounded-xl hover:bg-bg-light transition-colors">← Exams</a>
    </div>
@endsection

@section('content')

@if(session('success'))
    <div class="bg-stu-light border border-green-200 text-stu-dark text-sm rounded-xl px-4 py-3 mb-4">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Exam Details</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Level</span><span class="font-medium">{{ $exam->level?->title ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Questions</span><span class="font-medium">{{ $exam->total_questions }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Duration</span><span class="font-medium">{{ $exam->duration_minutes }} min</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Pass Mark</span><span class="font-medium text-fran">{{ number_format($exam->pass_percentage, 0) }}%</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Max Attempts</span><span class="font-medium">{{ $exam->max_attempts ?? 'Unlimited' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Audience</span><span class="font-medium">{{ $exam->franchise_id ? 'Single franchise' : 'All franchises' }}</span></div>
                @if($exam->scheduled_at)
                    <div class="flex justify-between"><span class="text-gray-500">Scheduled</span><span class="font-medium text-fran">{{ $exam->scheduled_at_ist->format('d M Y, H:i') }}</span></div>
                @endif
                @if($exam->expires_at)
                    <div class="flex justify-between"><span class="text-gray-500">Expires</span><span class="font-medium">{{ $exam->expires_at_ist->format('d M Y') }}</span></div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    @if($exam->is_active)
                        <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Active</span>
                    @else
                        <span class="text-xs bg-bg-mid text-gray-400 px-2 py-0.5 rounded-full">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

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

        <div class="bg-white rounded-2xl border border-border p-5">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Question Paper</h2>
            @if($exam->activePaper)
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $exam->activePaper->title ?: 'Question paper' }}</p>
                        <p class="text-xs text-gray-400">{{ $exam->activePaper->total_questions }} questions</p>
                    </div>
                    <form method="POST" action="{{ route('admin.exams.papers.destroy', [$exam, $exam->activePaper]) }}"
                          onsubmit="return confirm('Delete this question paper?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                    </form>
                </div>
                <p class="text-xs text-gray-400 mb-3">Uploading a new file below will replace this paper.</p>
            @else
                <p class="text-xs text-amber-600 mb-3">No question paper uploaded yet — students can't attempt this exam.</p>
            @endif

            @if(session('importErrors') && count(session('importErrors')))
                <div class="bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-xl px-3 py-2 mb-3">
                    <p class="font-semibold mb-1">Skipped rows:</p>
                    <ul class="list-disc list-inside space-y-0.5 max-h-32 overflow-y-auto">
                        @foreach(array_slice(session('importErrors'), 0, 10) as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.exams.papers.store', $exam) }}" enctype="multipart/form-data" class="space-y-2">
                @csrf
                <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
                       class="w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-fran-light file:text-fran">
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-xs font-semibold hover:bg-fran-dark">Upload Paper</button>
                    <a href="{{ route('admin.level-up-exam-papers.template') }}" class="text-xs text-fran hover:underline">Download template</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-admin">Recent Attempts</h2>
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
                <div class="px-5 py-12 text-center text-gray-400">No attempts yet.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
