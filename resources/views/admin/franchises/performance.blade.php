@extends('layouts.admin')
@section('title', 'Franchise Performance Comparison')
@section('page-title', 'Franchise Performance Comparison')

@section('breadcrumb')
    <a href="{{ route('admin.franchises.index') }}" class="text-fran hover:underline">Franchises</a>
    <span class="mx-1 text-gray-400">/</span>
    <span>Performance</span>
@endsection

@section('page-actions')
    <a href="{{ route('admin.franchises.index') }}"
       class="inline-flex items-center gap-2 border border-border text-gray-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-bg-light transition-colors">
        ← All Franchises
    </a>
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-fran text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-fran-dark transition-colors">
        Export
    </button>
@endsection

@section('content')

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-admin text-white text-xs uppercase tracking-wide">
                <th class="px-4 py-3 text-left w-12">Rank</th>
                <th class="px-4 py-3 text-left">Franchise</th>
                <th class="px-4 py-3 text-left">City</th>
                <th class="px-4 py-3 text-right">Students</th>
                <th class="px-4 py-3 text-right">Revenue</th>
                <th class="px-4 py-3 text-center">Growth</th>
                <th class="px-4 py-3 text-right">Attendance %</th>
                <th class="px-4 py-3 text-right">Avg Score</th>
                <th class="px-4 py-3 text-right">Pass Rate</th>
                <th class="px-4 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($franchises as $f)
                <tr class="hover:bg-bg-light transition-colors">
                    <td class="px-4 py-3">
                        @if($f->rank === 1)
                            <span class="text-lg">🥇</span>
                        @elseif($f->rank === 2)
                            <span class="text-lg">🥈</span>
                        @elseif($f->rank === 3)
                            <span class="text-lg">🥉</span>
                        @else
                            <span class="font-semibold text-gray-500">#{{ $f->rank }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-fran-light text-fran font-bold text-xs flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($f->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-admin text-sm">{{ $f->name }}</p>
                                <p class="text-xs text-gray-400">{{ $f->owner_name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $f->city }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-admin">{{ number_format($f->students_count) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-fran">₹{{ number_format($f->monthly_revenue) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($f->growth >= 0)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                                ↑ {{ $f->growth }}%
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">
                                ↓ {{ abs($f->growth) }}%
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $f->attendance_rate }}%</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $f->avg_score }}%</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $f->pass_rate }}%</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('admin.franchises.show', $f) }}"
                           class="text-xs text-fran font-medium hover:underline">View Details</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-12 text-center text-gray-400">No active franchises found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
