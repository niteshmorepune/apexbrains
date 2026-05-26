@extends('layouts.franchise')
@section('title', 'Progress Reports')
@section('page-title', 'Student Progress Reports')

@section('content')

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4">
    <form method="GET" action="{{ route('franchise.reports.index') }}" class="flex items-center gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search student..."
               class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran flex-1 min-w-40">
        <select name="sort" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="name" @selected(request('sort') !== 'best_score')>Sort by Name</option>
            <option value="best_score" @selected(request('sort') === 'best_score')>Sort by Best Score</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Filter</button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Exams</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Avg Score</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Last Score</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Trend</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
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
                            <span class="font-medium text-gray-800">{{ $s->full_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->currentLevel)
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full">L{{ $s->currentLevel->number }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $s->exam_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="font-semibold {{ $s->avg_score >= 75 ? 'text-stu' : ($s->avg_score >= 50 ? 'text-logo-amber' : 'text-red-500') }}">
                            {{ $s->exam_count ? number_format($s->avg_score, 1) . '%' : '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">
                        {{ $s->exam_count ? number_format($s->last_score, 1) . '%' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->exam_count >= 2)
                            <span class="{{ $s->last_score >= $s->avg_score ? 'text-stu' : 'text-red-400' }}">
                                {{ $s->last_score >= $s->avg_score ? '↑' : '↓' }}
                            </span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('franchise.reports.show', $s) }}" class="text-xs text-fran hover:underline">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-400">No students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
