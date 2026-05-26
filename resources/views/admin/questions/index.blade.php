@extends('layouts.admin')
@section('title', 'AI Question Bank')
@section('page-title', 'AI Question Bank')

@section('page-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.questions.audio') }}"
           class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
            Audio Generator
        </a>
        <a href="{{ route('admin.pdf-uploads.index') }}"
           class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
            PDF Upload
        </a>
        <a href="{{ route('admin.questions.create') }}"
           class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            + Add Question
        </a>
    </div>
@endsection

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-admin">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Questions</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-fran">{{ number_format($stats['mcq']) }}</p>
        <p class="text-xs text-gray-500 mt-1">MCQ</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-stu">{{ number_format($stats['audio']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Audio</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-logo-amber">{{ number_format($stats['pending']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Pending Review</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-gray-600">{{ number_format($stats['pdf_sources']) }}</p>
        <p class="text-xs text-gray-500 mt-1">PDF Sources</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4">
    <form method="GET" action="{{ route('admin.questions.index') }}" class="flex items-center gap-3 flex-wrap">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions..."
               class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran flex-1 min-w-48">
        <select name="level" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="">All Levels</option>
            @foreach($levels as $level)
                <option value="{{ $level->id }}" @selected(request('level') == $level->id)>Level {{ $level->number }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Filter</button>
        @if(request('search') || request('level'))
            <a href="{{ route('admin.questions.index', ['tab' => $tab]) }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>
</div>

{{-- Tab filters --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="flex border-b border-border">
        @foreach(['all' => 'All Questions', 'mcq' => 'MCQ', 'audio' => 'Audio', 'pending' => 'Pending Review'] as $key => $label)
            <a href="{{ route('admin.questions.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
               class="px-5 py-3 text-sm font-medium border-b-2 transition-colors
                      {{ $tab === $key ? 'border-fran text-fran' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
                @if($key === 'pending' && $stats['pending'] > 0)
                    <span class="ml-1 bg-logo-amber text-white text-xs rounded-full px-1.5 py-0.5">{{ $stats['pending'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Question</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Type</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Difficulty</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($questions as $q)
                <tr class="hover:bg-bg-light {{ $q->status === 'pending' ? 'bg-yellow-50' : '' }}">
                    <td class="px-5 py-3">
                        <p class="text-gray-800 line-clamp-2 max-w-xl">{{ $q->question_text }}</p>
                        @if($q->question_category)
                            <span class="text-xs text-gray-400">{{ $q->question_category }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($q->level)
                            <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full font-medium">L{{ $q->level->number }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($q->type === 'audio')
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Audio</span>
                        @else
                            <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full">MCQ</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs capitalize
                            {{ $q->difficulty === 'easy' ? 'text-stu' : ($q->difficulty === 'hard' ? 'text-red-500' : 'text-logo-amber') }}">
                            {{ $q->difficulty }}
                        </span>
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
                            @if($q->status === 'pending')
                                <form method="POST" action="{{ route('admin.questions.approve', $q) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-stu hover:underline font-medium">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.questions.reject', $q) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-500 hover:underline font-medium">Reject</button>
                                </form>
                            @else
                                <a href="{{ route('admin.questions.edit', $q) }}" class="text-xs text-fran hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.questions.destroy', $q) }}"
                                      onsubmit="return confirm('Delete this question?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                        No questions found. Add your first question or upload a PDF.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($questions->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">
                Showing {{ $questions->firstItem() }}–{{ $questions->lastItem() }} of {{ $questions->total() }} questions
            </span>
            {{ $questions->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
