@extends('layouts.franchise')
@section('title', 'Parent Directory')
@section('page-title', 'Parent Contact Directory')

@section('content')

{{-- Search + pill-tab level filter --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4 space-y-3">
    <form method="GET" action="{{ route('franchise.parents.index') }}" class="flex items-center gap-3">
        <input type="hidden" name="level" value="{{ request('level') }}">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search parent or student name..."
                   class="w-full pl-9 pr-4 py-2 border border-border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        </div>
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Search</button>
        @if(request('search') || request('level'))
            <a href="{{ route('franchise.parents.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>
    {{-- Level pill tabs --}}
    <div class="flex gap-2">
        @foreach(['' => 'All', '1-4' => 'L1–4', '5-8' => 'L5–8', '9-14' => 'L9+'] as $group => $label)
            <a href="{{ route('franchise.parents.index', array_merge(request()->except('level','page'), $group ? ['level' => $group] : [])) }}"
               class="px-3 py-1 rounded-full text-xs font-medium border transition-colors
                      {{ request('level', '') === $group ? 'bg-fran text-white border-fran' : 'border-border text-gray-600 hover:border-fran' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-fran">Parent Contacts ({{ $parents->total() }})</h2>
    </div>
    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">Parent Name</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Relationship</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">Mobile</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">WhatsApp</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($parents as $parent)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3">
                        <a href="{{ route('franchise.students.show', $parent->student) }}"
                           class="font-medium text-fran hover:underline text-sm">
                            {{ $parent->student?->full_name ?? '—' }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($parent->student?->currentLevel)
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">L{{ $parent->student->currentLevel->number }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $parent->name }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs capitalize text-gray-500 bg-bg-mid px-2 py-0.5 rounded-full">
                            {{ $parent->relationship ?: '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="tel:{{ $parent->phone }}" class="text-sm text-fran hover:underline">{{ $parent->phone }}</a>
                    </td>
                    <td class="px-4 py-3">
                        @if($parent->whatsapp)
                            <a href="https://wa.me/91{{ preg_replace('/\D/', '', $parent->whatsapp) }}"
                               target="_blank" class="text-sm text-stu hover:underline">{{ $parent->whatsapp }}</a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="tel:{{ $parent->phone }}" class="text-xs border border-border text-gray-600 px-2 py-1 rounded-lg hover:bg-bg-light transition-colors">Call</a>
                            @if($parent->whatsapp)
                                <a href="https://wa.me/91{{ preg_replace('/\D/', '', $parent->whatsapp) }}"
                                   target="_blank" class="text-xs bg-stu text-white px-2 py-1 rounded-lg hover:bg-stu-dark transition-colors">WhatsApp</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        No parents found. Register students to see their parent contacts here.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
    @if($parents->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">Showing {{ $parents->firstItem() }}–{{ $parents->lastItem() }} of {{ $parents->total() }}</span>
            {{ $parents->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
