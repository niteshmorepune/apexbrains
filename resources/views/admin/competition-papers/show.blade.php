@extends('layouts.admin')
@section('title', 'Paper #' . $paper->paper_number)
@section('page-title', $paper->title)

@section('page-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.competition-papers.edit', $paper) }}"
           class="px-4 py-2 bg-fran text-white text-sm font-semibold rounded-xl hover:bg-fran-dark transition-colors">
            Edit
        </a>
        <a href="{{ route('admin.competition-papers.index') }}"
           class="px-4 py-2 border border-border text-gray-600 text-sm font-semibold rounded-xl hover:bg-bg-light transition-colors">
            ← Papers
        </a>
    </div>
@endsection

@section('content')

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Paper</p>
        <p class="text-2xl font-bold text-admin">#{{ $paper->paper_number }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Questions</p>
        <p class="text-2xl font-bold text-fran">{{ number_format($paper->paper_questions_count) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Duration</p>
        <p class="text-2xl font-bold text-logo-amber">{{ $paper->duration_minutes }}<span class="text-sm font-medium text-gray-400"> min</span></p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Status</p>
        <p class="text-2xl font-bold {{ $paper->is_active ? 'text-stu' : 'text-gray-400' }}">
            {{ $paper->is_active ? 'Active' : 'Inactive' }}
        </p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-border p-6">
    <h2 class="text-sm font-semibold text-admin mb-4">Details</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500 mb-0.5">Level</dt><dd class="font-medium">{{ $paper->level ? 'Level ' . $paper->level->number . ' — ' . $paper->level->title : '—' }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Difficulty</dt><dd class="capitalize font-medium">{{ $paper->difficulty }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Practice Attempts</dt><dd class="font-medium">{{ number_format($paper->attempts()->count()) }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Created</dt><dd>{{ $paper->created_at->format('d M Y') }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Last Updated</dt><dd>{{ $paper->updated_at->format('d M Y') }}</dd></div>
        @if($paper->description)
            <div class="col-span-2"><dt class="text-gray-500 mb-0.5">Description</dt><dd class="text-gray-700">{{ $paper->description }}</dd></div>
        @endif
    </dl>
</div>

@endsection
