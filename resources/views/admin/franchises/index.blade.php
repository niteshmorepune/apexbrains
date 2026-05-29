@extends('layouts.admin')
@section('title', 'Franchise Management')
@section('page-title', 'Franchise Management')

@section('page-actions')
    <a href="{{ route('admin.franchises.approval-queue') }}"
       class="inline-flex items-center gap-2 border border-border text-gray-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-bg-light transition-colors">
        Approval Queue
    </a>
    <a href="{{ route('admin.franchises.performance') }}"
       class="inline-flex items-center gap-2 border border-border text-gray-600 text-sm font-medium px-4 py-2 rounded-xl hover:bg-bg-light transition-colors">
        Performance
    </a>
    <a href="{{ route('admin.franchises.create') }}"
       class="inline-flex items-center gap-2 bg-fran text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-fran-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add New
    </a>
@endsection

@section('content')

{{-- Search + Filter tabs --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4 flex flex-wrap items-center gap-3">
    <form method="GET" action="{{ route('admin.franchises.index') }}" class="flex items-center gap-3 flex-1">
        <div class="relative flex-1 max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                   class="w-full pl-9 pr-4 py-2 border border-border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
        </div>
        @foreach(['all' => 'All', 'active' => 'Active', 'pending' => 'Pending', 'suspended' => 'Suspended'] as $val => $label)
            <button type="submit" name="status" value="{{ $val }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-colors
                           {{ request('status', 'all') === $val
                               ? 'bg-fran text-white'
                               : 'bg-bg-light text-gray-600 hover:bg-bg-mid' }}">
                {{ $label }}
            </button>
        @endforeach
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-admin">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-white">Franchise</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">Owner</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">City</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Students</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Revenue</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-white">Avg Score</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">Joined</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($franchises as $f)
                    <tr class="hover:bg-bg-light transition-colors">
                        <td class="px-5 py-3">
                            <div class="font-medium text-admin">{{ $f->name }}</div>
                            <div class="text-xs text-gray-400">{{ $f->franchise_code }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $f->owner_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $f->city }}</td>
                        <td class="px-4 py-3 text-right font-medium text-admin">{{ number_format($f->students_count) }}</td>
                        <td class="px-4 py-3 text-right text-fran font-medium text-sm">₹{{ number_format($f->students_count * $f->fee_per_student) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600 text-sm">—</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $f->created_at->format('M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-status-badge :status="$f->status" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.franchises.show', $f) }}"
                                   class="text-fran text-xs font-medium hover:underline">View</a>
                                <a href="{{ route('admin.franchises.edit', $f) }}"
                                   class="text-gray-500 text-xs hover:underline">Edit</a>
                                @if($f->status === 'pending')
                                    <form method="POST" action="{{ route('admin.franchises.approve', $f) }}" class="inline">
                                        @csrf
                                        <button class="text-stu text-xs font-medium hover:underline">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.franchises.reject', $f) }}" class="inline"
                                          onsubmit="return confirm('Reject this franchise application?')">
                                        @csrf
                                        <button class="text-red-500 text-xs hover:underline">Reject</button>
                                    </form>
                                @elseif($f->status === 'active')
                                    <form method="POST" action="{{ route('admin.franchises.suspend', $f) }}" class="inline">
                                        @csrf
                                        <button class="text-red-500 text-xs hover:underline">Suspend</button>
                                    </form>
                                @elseif($f->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.franchises.approve', $f) }}" class="inline">
                                        @csrf
                                        <button class="text-stu text-xs font-medium hover:underline">Reactivate</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                            No franchises found.
                            <a href="{{ route('admin.franchises.create') }}" class="text-fran hover:underline ml-1">Add the first one →</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($franchises->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing {{ $franchises->firstItem() }}–{{ $franchises->lastItem() }} of {{ $franchises->total() }} franchises
            </p>
            {{ $franchises->links('components.pagination') }}
        </div>
    @endif
</div>

@endsection
