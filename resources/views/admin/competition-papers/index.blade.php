@extends('layouts.admin')
@section('title', 'Practice Papers')
@section('page-title', 'Competition Practice Papers')

@section('page-actions')
    <a href="{{ route('admin.competition-papers.create') }}"
       class="inline-flex items-center gap-2 bg-fran text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-fran-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Paper
    </a>
@endsection

@section('content')

{{-- Filter bar --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4 flex items-center gap-3">
    <form method="GET" action="{{ route('admin.competition-papers.index') }}" class="flex items-center gap-3">
        @foreach(['all' => 'All', 'easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'] as $val => $label)
            <button type="submit" name="difficulty" value="{{ $val === 'all' ? '' : $val }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-colors
                           {{ request('difficulty', '') === ($val === 'all' ? '' : $val)
                               ? 'bg-fran text-white'
                               : 'bg-bg-light text-gray-600 hover:bg-bg-mid' }}">
                {{ $label }}
            </button>
        @endforeach
    </form>
    <span class="ml-auto text-sm text-gray-400">{{ number_format($papers->total()) }} papers total</span>
</div>

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-center px-4 py-3 text-xs font-semibold text-white w-16">#</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Title</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Questions</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Duration</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Difficulty</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($papers as $paper)
                <tr class="hover:bg-bg-light">
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-bold text-admin">{{ $paper->paper_number }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-admin">{{ $paper->title }}</p>
                        @if($paper->description)
                            <p class="text-xs text-gray-400 line-clamp-1">{{ $paper->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-medium text-gray-700">{{ $paper->total_questions }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $paper->duration_minutes }} min</td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $diffColor = ['easy' => 'text-stu', 'medium' => 'text-logo-amber', 'hard' => 'text-red-500'][$paper->difficulty];
                        @endphp
                        <span class="text-xs capitalize font-medium {{ $diffColor }}">{{ $paper->difficulty }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($paper->is_active)
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Active</span>
                        @else
                            <span class="text-xs bg-bg-mid text-gray-400 px-2 py-0.5 rounded-full">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.competition-papers.edit', $paper) }}"
                               class="text-xs text-fran hover:underline font-medium">Edit</a>
                            <form method="POST" action="{{ route('admin.competition-papers.destroy', $paper) }}"
                                  onsubmit="return confirm('Delete Paper #{{ $paper->paper_number }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        No practice papers yet.
                        <a href="{{ route('admin.competition-papers.create') }}" class="text-fran hover:underline ml-1">
                            Create Paper #1 →
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($papers->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">
                Showing {{ $papers->firstItem() }}–{{ $papers->lastItem() }} of {{ $papers->total() }}
            </span>
            {{ $papers->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
