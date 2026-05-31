@extends('layouts.admin')
@section('title', 'Competitions')
@section('page-title', 'Competition Management')

@section('page-actions')
    <a href="{{ route('admin.competitions.create') }}"
       class="inline-flex items-center gap-2 bg-fran text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-fran-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Competition
    </a>
@endsection

@section('content')

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4 flex items-center gap-3">
    <form method="GET" action="{{ route('admin.competitions.index') }}" class="flex items-center gap-3">
        @foreach(['all' => 'All', 'local' => 'Local', 'regional' => 'Regional', 'national' => 'National'] as $val => $label)
            <button type="submit" name="type" value="{{ $val === 'all' ? '' : $val }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-colors
                           {{ request('type', '') === ($val === 'all' ? '' : $val)
                               ? 'bg-fran text-white'
                               : 'bg-bg-light text-gray-600 hover:bg-bg-mid' }}">
                {{ $label }}
            </button>
        @endforeach
    </form>
    <a href="{{ route('admin.competition-papers.index') }}"
       class="ml-auto text-sm text-fran hover:underline font-medium">
        Practice Papers →
    </a>
</div>

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Competition</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Type</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Dates</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Registrations</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">External</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($competitions as $c)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3">
                        <p class="font-medium text-admin">{{ $c->title }}</p>
                        @if($c->description)
                            <p class="text-xs text-gray-400 line-clamp-1">{{ $c->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $typeColor = ['local' => 'bg-stu-light text-stu-dark', 'regional' => 'bg-blue-50 text-fran', 'national' => 'bg-yellow-50 text-yellow-700'][$c->competition_type] ?? 'bg-bg-mid text-gray-600';
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $typeColor }} capitalize">
                            {{ $c->competition_type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-600">
                        <p>{{ $c->start_date->format('d M Y') }}</p>
                        <p class="text-gray-400">to {{ $c->end_date->format('d M Y') }}</p>
                    </td>
                    <td class="px-4 py-3 text-center font-medium text-admin">{{ $c->registrations_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($c->is_open_to_external)
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Open</span>
                        @else
                            <span class="text-xs bg-bg-mid text-gray-400 px-2 py-0.5 rounded-full">Internal</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($c->is_active)
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Active</span>
                        @else
                            <span class="text-xs bg-bg-mid text-gray-400 px-2 py-0.5 rounded-full">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.competitions.edit', $c) }}"
                               class="text-xs text-fran hover:underline font-medium">Edit</a>
                            <form method="POST" action="{{ route('admin.competitions.destroy', $c) }}"
                                  onsubmit="return confirm('Delete this competition?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        No competitions yet.
                        <a href="{{ route('admin.competitions.create') }}" class="text-fran hover:underline ml-1">
                            Create the first one →
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>

    @if($competitions->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">
                Showing {{ $competitions->firstItem() }}–{{ $competitions->lastItem() }} of {{ $competitions->total() }}
            </span>
            {{ $competitions->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
