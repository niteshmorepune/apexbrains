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
        <input type="hidden" name="status" value="{{ $status }}">
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
        {{-- Level pills --}}
        <span class="text-xs text-gray-500 mr-1">Filter by Level:</span>
        @foreach(['' => 'All', '1-3' => 'L1-3', '4-6' => 'L4-6', '7-9' => 'L7-9', '10' => 'L10+'] as $group => $label)
            <a href="{{ route('franchise.students.index', array_merge(request()->except('level_group','page'), $group ? ['level_group' => $group] : [])) }}"
               class="px-3 py-1 rounded-full text-xs font-medium border transition-colors
                      {{ request('level_group', '') === $group ? 'bg-fran text-white border-fran' : 'border-border text-gray-600 hover:border-fran' }}">
                {{ $label }}
            </a>
        @endforeach
        <span class="text-gray-300 text-xs mx-1">|</span>
        {{-- Type pills --}}
        <span class="text-xs text-gray-500 mr-1">Type:</span>
        @foreach(['all' => "All ({$allCount})", 'internal' => "Internal ({$internalCount})", 'external' => "External ({$externalCount})"] as $type => $label)
            <a href="{{ route('franchise.students.index', array_merge(request()->except('tab','page'), ['tab' => $type])) }}"
               class="px-3 py-1 rounded-full text-xs font-medium border transition-colors
                      {{ $tab === $type ? 'bg-fran text-white border-fran' : 'border-border text-gray-600 hover:border-fran' }}">
                {{ $label }}
            </a>
        @endforeach
        <span class="text-gray-300 text-xs mx-1">|</span>
        {{-- Status pills --}}
        <span class="text-xs text-gray-500 mr-1">Status:</span>
        @foreach(['active' => "Active ({$activeCount})", 'inactive' => "Inactive ({$inactiveCount})", 'all' => 'All'] as $st => $label)
            <a href="{{ route('franchise.students.index', array_merge(request()->except('status','page'), ['status' => $st])) }}"
               class="px-3 py-1 rounded-full text-xs font-medium border transition-colors
                      {{ $status === $st ? 'bg-fran text-white border-fran' : 'border-border text-gray-600 hover:border-fran' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

{{-- Student table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-fran">
            {{ ['all' => 'All Students', 'internal' => 'Internal Students', 'external' => 'External Students'][$tab] }}
        </h2>
        <span class="text-xs text-gray-400">{{ $students->total() }} students</span>
    </div>
    <div class="overflow-x-auto"><table class="w-full min-w-[980px] text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Photo</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student Name</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">Parent Contact</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Enrolled</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Last Score</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Student Type</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($students as $s)
                @php $lastScore = $s->examAttempts->first()?->percentage; @endphp
                <tr class="hover:bg-bg-light">
                    <td class="px-4 py-3">
                        <div class="flex justify-center">
                            <div class="w-9 h-9 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($s->first_name, 0, 1) . substr($s->last_name, 0, 1)) }}
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $s->full_name }}</p>
                        <p class="font-mono text-xs text-gray-400">{{ $s->student_code }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->currentLevel)
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">L{{ $s->currentLevel->number }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        {{ $s->primaryParent?->phone ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $s->enrollment_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center text-xs font-semibold text-gray-700">
                        {{ $lastScore !== null ? round($lastScore) . '%' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->student_type === 'internal')
                            <span class="text-xs border border-green-400 text-green-600 px-2 py-0.5 rounded-full font-medium">Internal</span>
                        @else
                            <span class="text-xs border border-red-400 text-red-500 px-2 py-0.5 rounded-full font-medium">External</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->is_active)
                            <span class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium">Active</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('franchise.students.show', $s) }}" class="text-xs text-fran hover:underline">View</a>
                            <a href="{{ route('franchise.fees.record', ['student_id' => $s->id]) }}" class="text-xs text-fran hover:underline">Fee</a>
                            @if($s->student_type === 'internal')
                                <a href="{{ route('franchise.promotions.index') }}" class="text-xs text-fran hover:underline">Promote</a>
                            @endif
                            <form method="POST" action="{{ route('franchise.students.destroy', $s) }}"
                                  onsubmit="return confirm('Delete {{ $s->full_name }}? This removes them from your roster.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-5 py-10 text-center text-gray-400">
                        No students found. <a href="{{ route('franchise.students.create') }}" class="text-fran underline">Register your first student</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
    @if($students->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }}</span>
            {{ $students->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
