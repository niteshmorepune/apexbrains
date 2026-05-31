@extends('layouts.admin')
@section('title', 'Global Leaderboard')
@section('page-title', 'Global Leaderboard')

@section('content')

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-6">
    <form method="GET" action="{{ route('admin.leaderboard') }}" class="flex items-center gap-3 flex-wrap">
        <select name="franchise" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="">All Cities</option>
            @foreach($franchises as $f)
                <option value="{{ $f->id }}" @selected($franchiseFilter == $f->id)>{{ $f->name }}</option>
            @endforeach
        </select>
        <select name="level" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="">All Levels</option>
            @foreach($levels as $l)
                <option value="{{ $l->id }}" @selected($levelFilter == $l->id)>{{ $l->title }}</option>
            @endforeach
        </select>
        <select name="period" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="all" @selected($periodFilter === 'all')>All Time</option>
            <option value="month" @selected($periodFilter === 'month')>This Month</option>
            <option value="week" @selected($periodFilter === 'week')>This Week</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Filter</button>
        @if($franchiseFilter || $levelFilter || $periodFilter !== 'all')
            <a href="{{ route('admin.leaderboard') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>
</div>

{{-- Top 3 podium --}}
@if($rows->isNotEmpty())
@php
    $podium = $rows->values();
    $first  = $podium->get(0);
    $second = $podium->get(1);
    $third  = $podium->get(2);
@endphp
<div class="flex items-end justify-center gap-6 mb-8">
    {{-- 2nd place --}}
    @if($second)
    <div class="text-center w-36">
        <div class="w-14 h-14 rounded-full bg-gray-200 flex items-center justify-center text-xl font-bold text-gray-600 mx-auto mb-2">
            {{ strtoupper(substr($second->student?->first_name ?? 'S', 0, 1)) }}
        </div>
        <p class="text-sm font-semibold text-admin truncate">{{ $second->student?->full_name ?? 'Unknown' }}</p>
        <p class="text-xs text-gray-500 truncate">{{ $second->student?->franchise?->name }}</p>
        <p class="text-lg font-bold text-gray-500 mt-1">{{ number_format($second->avg_score, 1) }}%</p>
        <div class="h-16 bg-gray-300 rounded-t-xl flex items-end justify-center pb-2 mt-2">
            <span class="text-2xl font-black text-gray-500">2</span>
        </div>
    </div>
    @endif

    {{-- 1st place --}}
    <div class="text-center w-36">
        <div class="w-3 h-3 rounded-full bg-logo-amber mx-auto mb-1"></div>
        <div class="w-16 h-16 rounded-full bg-logo-amber flex items-center justify-center text-2xl font-bold text-white mx-auto mb-2 ring-4 ring-yellow-200">
            {{ strtoupper(substr($first->student?->first_name ?? 'S', 0, 1)) }}
        </div>
        <p class="text-sm font-bold text-admin truncate">{{ $first->student?->full_name ?? 'Unknown' }}</p>
        <p class="text-xs text-gray-500 truncate">{{ $first->student?->franchise?->name }}</p>
        <p class="text-xl font-black text-logo-amber mt-1">{{ number_format($first->avg_score, 1) }}%</p>
        <div class="h-24 bg-logo-amber rounded-t-xl flex items-end justify-center pb-2 mt-2">
            <span class="text-3xl font-black text-white">1</span>
        </div>
    </div>

    {{-- 3rd place --}}
    @if($third)
    <div class="text-center w-36">
        <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center text-xl font-bold text-orange-600 mx-auto mb-2">
            {{ strtoupper(substr($third->student?->first_name ?? 'S', 0, 1)) }}
        </div>
        <p class="text-sm font-semibold text-admin truncate">{{ $third->student?->full_name ?? 'Unknown' }}</p>
        <p class="text-xs text-gray-500 truncate">{{ $third->student?->franchise?->name }}</p>
        <p class="text-lg font-bold text-orange-600 mt-1">{{ number_format($third->avg_score, 1) }}%</p>
        <div class="h-12 bg-orange-300 rounded-t-xl flex items-end justify-center pb-2 mt-2">
            <span class="text-2xl font-black text-orange-700">3</span>
        </div>
    </div>
    @endif
</div>
@endif

{{-- Full rankings table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border">
        <h2 class="text-sm font-semibold text-admin">Full Rankings</h2>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-center px-4 py-3 text-xs font-semibold text-white w-12">Rank</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">Branch</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">City</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Avg Score</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Speed</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Exams</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Badge</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Profile</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($rows as $i => $row)
                @php
                    $rank = $i + 1;
                    $badge = match(true) {
                        $rank === 1 => ['🥇', 'bg-yellow-50 text-yellow-700'],
                        $rank === 2 => ['🥈', 'bg-gray-50 text-gray-600'],
                        $rank === 3 => ['🥉', 'bg-orange-50 text-orange-700'],
                        $row->avg_score >= 90 => ['⭐', 'bg-stu-light text-stu-dark'],
                        $row->avg_score >= 75 => ['✓', 'bg-bg-mid text-gray-500'],
                        default => ['—', 'bg-transparent text-gray-400'],
                    };
                @endphp
                <tr class="hover:bg-bg-light {{ $rank <= 3 ? 'font-medium' : '' }}">
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-bold {{ $rank === 1 ? 'text-logo-amber' : ($rank <= 3 ? 'text-gray-600' : 'text-gray-400') }}">
                            {{ $rank }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-fran flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                                {{ strtoupper(substr($row->student?->first_name ?? 'S', 0, 1)) }}
                            </div>
                            <span class="text-admin">{{ $row->student?->full_name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($row->student?->currentLevel)
                            <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full font-medium">
                                L{{ $row->student->currentLevel->number }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $row->student?->franchise?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $row->student?->franchise?->city ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="font-bold {{ $row->avg_score >= 90 ? 'text-stu' : ($row->avg_score >= 75 ? 'text-fran' : 'text-gray-600') }}">
                            {{ number_format($row->avg_score, 1) }}%
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500 text-xs">
                        @if($row->avg_seconds)
                            @php $s = (int) $row->avg_seconds; @endphp
                            {{ $s >= 60 ? floor($s/60).'m '.($s%60).'s' : $s.'s' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $row->exam_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $badge[1] }}">{{ $badge[0] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($row->student)
                            <a href="{{ route('admin.students.show', $row->student_id) }}" class="text-xs text-fran hover:underline">View Profile</a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-5 py-12 text-center text-gray-400">
                        No exam data yet. Leaderboard will populate as students complete exams.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
