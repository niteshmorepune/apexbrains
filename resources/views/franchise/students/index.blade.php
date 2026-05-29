@extends('layouts.franchise')
@section('title', 'Students')
@section('page-title', 'Student List')

@section('page-actions')
    <a href="{{ route('franchise.students.import.page') }}"
       class="px-3 py-2 border border-white text-white rounded-xl text-xs font-medium hover:bg-blue-600 transition-colors">
        Bulk Import
    </a>
    <a href="{{ route('franchise.students.create') }}"
       class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50 transition-colors">
        + Register
    </a>
@endsection

@section('content')

{{-- Filters (combined: search + type pills + level pills) --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4 space-y-3">
    <form method="GET" action="{{ route('franchise.students.index') }}" id="studentFilter" class="flex items-center gap-3">
        <input type="hidden" name="level_group" value="{{ request('level_group') }}">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or code..."
                   class="w-full pl-9 pr-4 py-2 border border-border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        </div>
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Search</button>
    </form>
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Type pills --}}
        @foreach(['internal' => "Internal ({$internalCount})", 'external' => "External ({$externalCount})"] as $type => $label)
            <a href="{{ route('franchise.students.index', array_merge(request()->except('tab','page'), ['tab' => $type])) }}"
               class="px-3 py-1 rounded-full text-xs font-medium border transition-colors
                      {{ $tab === $type ? 'bg-fran text-white border-fran' : 'border-border text-gray-600 hover:border-fran' }}">
                {{ $label }}
            </a>
        @endforeach
        <span class="text-gray-300 text-xs">|</span>
        {{-- Level pills --}}
        @foreach(['' => 'All', '1-3' => 'L1-3', '4-6' => 'L4-6', '7-9' => 'L7-9', '10' => 'L10+'] as $group => $label)
            <a href="{{ route('franchise.students.index', array_merge(request()->except('level_group','page'), $group ? ['level_group' => $group] : [])) }}"
               class="px-3 py-1 rounded-full text-xs font-medium border transition-colors
                      {{ request('level_group', '') === $group ? 'bg-fran text-white border-fran' : 'border-border text-gray-600 hover:border-fran' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

{{-- Student table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-fran">
            {{ $tab === 'external' ? 'External Students' : 'Internal Students' }}
        </h2>
        <span class="text-xs text-gray-400">{{ $students->total() }} students</span>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Code</th>
                @if($tab === 'internal')
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                @else
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Competition</th>
                @endif
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Enrolled</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($students as $s)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($s->first_name, 0, 1)) }}
                            </div>
                            <p class="font-medium text-gray-800">{{ $s->full_name }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center font-mono text-xs text-gray-500">{{ $s->student_code }}</td>
                    @if($tab === 'internal')
                        <td class="px-4 py-3 text-center">
                            @if($s->currentLevel)
                                <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">L{{ $s->currentLevel->number }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    @else
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full font-medium">External</span>
                        </td>
                    @endif
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $s->enrollment_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('franchise.students.show', $s) }}" class="text-xs text-fran hover:underline">View</a>
                            <a href="{{ route('franchise.students.edit', $s) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                        No students found. <a href="{{ route('franchise.students.create') }}" class="text-fran underline">Register your first student</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($students->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }}</span>
            {{ $students->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
