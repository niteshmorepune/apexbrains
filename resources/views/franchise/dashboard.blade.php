@extends('layouts.franchise')
@section('title', 'Dashboard')
@section('page-title', 'Branch Dashboard')

@section('content')

{{-- Greeting --}}
<p class="text-sm text-gray-500 mb-5">
    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
    <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span>!
    &nbsp;·&nbsp; {{ auth()->user()->franchise->name ?? '' }}, {{ auth()->user()->franchise->city ?? '' }}
    &nbsp;·&nbsp; {{ now()->format('d M Y') }}
</p>

{{-- KPI Cards (colored accent stripe, per Figma) --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border border-l-4 border-l-fran p-5">
        <p class="text-xs text-gray-500 mb-1">Internal Students</p>
        <p class="text-2xl font-bold text-fran">{{ $internalStudents }}</p>
        <p class="text-xs text-stu mt-1">{{ $newThisMonth }} new this month</p>
    </div>
    <div class="bg-white rounded-2xl border border-border border-l-4 border-l-stu p-5">
        <p class="text-xs text-gray-500 mb-1">External Students</p>
        <p class="text-2xl font-bold text-stu">{{ $externalStudents }}</p>
        <p class="text-xs text-gray-400 mt-1">Competition-only learners</p>
    </div>
    <div class="bg-white rounded-2xl border border-border border-l-4 border-l-logo-amber p-5">
        <p class="text-xs text-gray-500 mb-1">Fees Collected</p>
        <p class="text-2xl font-bold text-logo-amber">₹{{ number_format($monthRevenue) }}</p>
        <p class="text-xs {{ $pendingFees > 0 ? 'text-logo-amber' : 'text-gray-400' }} mt-1">{{ $pendingFees }} payments pending</p>
    </div>
    <div class="bg-white rounded-2xl border border-border border-l-4 border-l-logo-red p-5">
        <p class="text-xs text-gray-500 mb-1">Promotions Due</p>
        <p class="text-2xl font-bold text-logo-red">{{ $promotionsDue }}</p>
        <p class="text-xs text-gray-400 mt-1">Students exam-eligible</p>
    </div>
</div>

{{-- Middle row: Students by Level + Recent Activity --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Students by Level chart --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-fran mb-4">Students by Level</h2>
        @php
            $barColors = ['#90CAF9', '#26C6DA', '#FBBC05', '#EA4335', '#1A73E8', '#FB8C00', '#1A2A6C'];
            $maxLevel  = max(1, $byLevel->max());
        @endphp
        @if($byLevel->sum() > 0)
            <div class="flex items-end gap-3 sm:gap-4 h-40">
                @foreach($byLevel as $label => $count)
                    @php $heightPx = 8 + ($count / $maxLevel) * 120; @endphp
                    <div class="flex-1 h-full flex flex-col items-center justify-end gap-1.5">
                        <span class="text-xs font-bold text-gray-700">{{ $count }}</span>
                        <div class="w-full max-w-[44px] rounded-t-md transition-all"
                             style="height: {{ $heightPx }}px; background-color: {{ $barColors[$loop->index % count($barColors)] }}"></div>
                        <span class="text-[11px] text-gray-400">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-12">No students enrolled yet.</p>
        @endif
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-fran mb-4">Recent Activity</h2>
        <div class="space-y-3.5">
            @php $dotColors = ['bg-fran', 'bg-stu', 'bg-logo-amber', 'bg-gray-800']; @endphp
            @forelse($recentActivity as $log)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full {{ $dotColors[$loop->index % count($dotColors)] }} mt-1.5 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0 flex items-start justify-between gap-2">
                        <p class="text-xs text-gray-700 leading-tight capitalize">{{ str_replace('_', ' ', $log->action) }}</p>
                        <p class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">{{ $log->created_at->diffForHumans(null, true) }}</p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400">No recent activity.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Student Overview Table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-fran">Student Overview</h2>
        <div class="flex items-center gap-3">
            <a href="{{ route('franchise.students.create') }}"
               class="text-xs bg-fran text-white px-3 py-1.5 rounded-lg font-medium hover:bg-fran-dark transition-colors">Register</a>
            <a href="{{ route('franchise.students.index') }}"
               class="text-xs text-fran hover:underline font-medium">View All →</a>
        </div>
    </div>
    <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Last Exam</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Fee</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Score</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($students as $s)
                @php
                    $lastExam = $s->examAttempts->first();
                    $fee      = $s->fees->first();
                    $feeMap   = [
                        'paid'    => ['Paid', 'bg-green-50 text-stu'],
                        'partial' => ['Partial', 'bg-orange-50 text-orange-500'],
                        'overdue' => ['Overdue', 'bg-red-50 text-logo-red'],
                        'pending' => ['Due', 'bg-amber-50 text-logo-amber'],
                    ];
                    $feeBadge = $fee ? ($feeMap[$fee->status] ?? ['—', 'text-gray-400']) : ['—', 'text-gray-400'];
                @endphp
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($s->first_name, 0, 1)) }}
                            </div>
                            <a href="{{ route('franchise.students.show', $s) }}"
                               class="font-medium text-fran hover:underline">{{ $s->full_name }}</a>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->currentLevel)
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">L{{ $s->currentLevel->number }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $lastExam ? $lastExam->created_at->format('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $feeBadge[1] }}">{{ $feeBadge[0] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-gray-700">
                        {{ $lastExam && $lastExam->percentage !== null ? number_format($lastExam->percentage, 1) . '%' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <a href="{{ route('franchise.students.show', $s) }}" class="text-xs text-fran hover:underline">View</a>
                        <span class="text-gray-300 mx-1">·</span>
                        <a href="{{ route('franchise.fees.record', ['student_id' => $s->id]) }}" class="text-xs text-fran hover:underline">Record Fee</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-gray-400">
                        No students yet. <a href="{{ route('franchise.students.create') }}" class="text-fran underline">Register your first student</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@endsection
