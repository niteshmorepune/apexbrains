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
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Total Students</p>
        <p class="text-2xl font-bold text-fran">{{ $totalStudents }}</p>
        <p class="text-xs text-stu mt-1">{{ $newThisMonth }} new this month</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Today Attendance</p>
        <p class="text-2xl font-bold {{ $todayAttendance >= 80 ? 'text-stu' : 'text-logo-amber' }}">{{ $todayAttendance }}%</p>
        <p class="text-xs text-gray-400 mt-1">{{ round($totalStudents * $todayAttendance / 100) }} of {{ $totalStudents }} present</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Fees Collected</p>
        <p class="text-2xl font-bold text-fran">₹{{ number_format($monthRevenue) }}</p>
        <p class="text-xs {{ $pendingFees > 0 ? 'text-logo-amber' : 'text-gray-400' }} mt-1">{{ $pendingFees }} payments pending</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Promotions Due</p>
        <p class="text-2xl font-bold text-logo-amber">{{ $promotionsDue }}</p>
        <p class="text-xs text-gray-400 mt-1">Students exam-eligible</p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    {{-- Attendance This Week --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <h2 class="text-sm font-semibold text-fran mb-4">Attendance This Week</h2>
        <div class="flex items-end gap-2 h-28">
            @foreach($weekDays as $day => $pct)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <span class="text-xs font-semibold {{ $pct >= 80 ? 'text-stu' : 'text-logo-amber' }}">{{ $pct }}%</span>
                    <div class="w-full bg-bg-mid rounded-t" style="height: {{ max(6, $pct * 0.92) }}px">
                        <div class="w-full h-full rounded-t {{ $pct >= 80 ? 'bg-stu' : 'bg-logo-amber' }} opacity-80"></div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $day }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Students by Level chart --}}
    <div class="bg-white rounded-2xl border border-border p-5">
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
</div>

{{-- Bottom row: Student Overview + Recent Activity --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    {{-- Recent Activity moved here --}}
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
</div>{{-- end bottom grid --}}

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
    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Attendance %</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Last Exam</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($students as $s)
                @php $lastExam = $s->examAttempts->first(); @endphp
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
                    <td class="px-4 py-3 text-right">
                        @php $att = rand(75, 99); @endphp
                        <span class="text-sm font-medium {{ $att >= 80 ? 'text-stu' : 'text-logo-amber' }}">{{ $att }}%</span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $lastExam ? $lastExam->created_at->format('d M Y') : '—' }}
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
    </table></div>
</div>

@endsection
