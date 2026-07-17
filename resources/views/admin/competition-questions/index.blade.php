@extends('layouts.admin')
@section('title', 'Competition Question Bank')
@section('page-title', 'Competition Question Bank')

@section('page-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.competition-questions.taxonomy') }}"
           class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
            Categories &amp; Types
        </a>
        <a href="{{ route('admin.competition-questions.import') }}"
           class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
            Bulk Import
        </a>
        <a href="{{ route('admin.competition-questions.create') }}"
           class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            + Add Question
        </a>
    </div>
@endsection

@section('content')

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-admin">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Questions</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-stu-dark">{{ number_format($stats['approved']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Approved</p>
    </div>
</div>

<div class="grid grid-cols-[240px_1fr] gap-5 items-start">

<div class="bg-white rounded-2xl border border-border p-5 sticky top-5">
    <form method="GET" action="{{ route('admin.competition-questions.index') }}" id="filterForm">
        <div class="mb-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions..."
                   class="w-full border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        </div>

        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">By Category</p>
        <div class="flex flex-wrap gap-1.5 mb-4">
            <label class="cursor-pointer">
                <input type="radio" name="category" value="" class="sr-only peer"
                       {{ !request('category') ? 'checked' : '' }}
                       onchange="document.getElementById('filterForm').submit()">
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium border transition-colors
                             peer-checked:bg-fran peer-checked:text-white peer-checked:border-fran
                             border-border text-gray-600 hover:border-fran">All</span>
            </label>
            @foreach($categories as $category)
                <label class="cursor-pointer">
                    <input type="radio" name="category" value="{{ $category->id }}" class="sr-only peer"
                           {{ request('category') == $category->id ? 'checked' : '' }}
                           onchange="document.getElementById('filterForm').submit()">
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium border transition-colors
                                 peer-checked:bg-fran peer-checked:text-white peer-checked:border-fran
                                 border-border text-gray-600 hover:border-fran">{{ $category->name }}</span>
                </label>
            @endforeach
        </div>

        <button type="submit" class="w-full py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            Apply Filters
        </button>
    </form>
</div>

<div>
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-4 py-3 text-xs font-semibold text-white w-20">ID</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Question</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Category / Type</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($questions as $q)
                <tr class="hover:bg-bg-light {{ $q->status === 'pending' ? 'bg-yellow-50' : '' }}">
                    <td class="px-4 py-3"><span class="text-xs font-mono text-gray-400">Q-{{ str_pad($q->id, 4, '0', STR_PAD_LEFT) }}</span></td>
                    <td class="px-5 py-3"><p class="text-gray-800 line-clamp-2 max-w-lg">{{ $q->question_text }}</p></td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full font-medium">{{ $q->category->name }}</span>
                        <p class="text-[11px] text-gray-400 mt-1">{{ $q->type->name }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($q->status === 'approved')
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Approved</span>
                        @elseif($q->status === 'pending')
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Pending</span>
                        @else
                            <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Rejected</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.competition-questions.edit', $q) }}" class="text-xs text-fran hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.competition-questions.destroy', $q) }}"
                                  onsubmit="return confirm('Delete this question?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">No questions found. Add your first question or bulk import them.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @if($questions->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">Showing {{ $questions->firstItem() }}–{{ $questions->lastItem() }} of {{ $questions->total() }} questions</span>
            {{ $questions->links('pagination::tailwind') }}
        </div>
    @endif
</div>
</div>
</div>
@endsection
