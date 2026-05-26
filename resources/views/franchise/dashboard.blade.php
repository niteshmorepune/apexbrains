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

{{-- KPI Cards --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Total Students</p>
        <p class="text-2xl font-bold text-fran">{{ $totalStudents }}</p>
        <p class="text-xs text-gray-400 mt-1">Active enrollments</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">This Month Revenue</p>
        <p class="text-2xl font-bold text-stu">₹{{ number_format($monthRevenue) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ now()->format('M Y') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Pending Fees</p>
        <p class="text-2xl font-bold {{ $pendingFees > 0 ? 'text-logo-amber' : 'text-stu' }}">{{ $pendingFees }}</p>
        <p class="text-xs text-gray-400 mt-1">Outstanding</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Active Exams</p>
        <p class="text-2xl font-bold text-fran">{{ $upcomingExams }}</p>
        <p class="text-xs text-gray-400 mt-1">Scheduled</p>
    </div>
</div>

<div class="grid grid-cols-3 gap-4 mb-6">
    {{-- Students by Level chart --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-fran mb-4">Students by Level</h2>
        @if($byLevel->sum() > 0)
            <div class="flex items-end gap-3 h-32">
                @foreach($byLevel as $label => $count)
                    @php $pct = $byLevel->max() > 0 ? ($count / $byLevel->max()) * 100 : 0; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs font-semibold text-fran">{{ $count }}</span>
                        <div class="w-full bg-bg-mid rounded-t-lg" style="height: {{ max(4, $pct * 0.96) }}%">
                            <div class="w-full h-full bg-fran rounded-t-lg opacity-80"></div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-8">No students enrolled yet.</p>
        @endif
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-fran mb-4">Recent Activity</h2>
        <div class="space-y-3">
            @forelse($recentActivity as $log)
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 rounded-full bg-fran mt-1.5 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-700 leading-tight">{{ str_replace('_', ' ', $log->action) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
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
        <a href="{{ route('franchise.students.index') }}"
           class="text-xs text-fran hover:underline font-medium">View All →</a>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Enrolled</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($students as $s)
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
                        {{ $s->enrollment_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs {{ $s->is_active ? 'text-stu' : 'text-gray-400' }}">
                            {{ $s->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-8 text-center text-gray-400">
                        No students yet. <a href="{{ route('franchise.students.create') }}" class="text-fran underline">Register your first student</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
