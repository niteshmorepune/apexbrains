@extends('layouts.admin')
@section('title', 'Upload Competition Paper')
@section('page-title', 'Upload Paper — ' . $competition->title)

@section('content')
<div class="max-w-2xl">

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if(session('importErrors') && count(session('importErrors')))
        <div class="bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-xl px-4 py-3 mb-4">
            <p class="font-semibold mb-1">Skipped rows:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach(array_slice(session('importErrors'), 0, 10) as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.competitions.papers.store', $competition) }}" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-2xl border border-border p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-admin">Paper Details</h2>
                <a href="{{ route('admin.competition-question-papers.template') }}"
                   class="text-xs text-fran hover:underline font-medium">Download CSV template</a>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. National Round 1 — Level 3"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Level <span class="text-red-500">*</span></label>
                        <select name="level_id" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('level_id') border-red-400 @enderror">
                            <option value="">Select…</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>
                                    Level {{ $level->number }}
                                </option>
                            @endforeach
                        </select>
                        @error('level_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (min) <span class="text-red-500">*</span></label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 10) }}" min="1" max="180" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pass % <span class="text-red-500">*</span></label>
                        <input type="number" name="pass_percentage" value="{{ old('pass_percentage', 75) }}" min="1" max="100" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Questions CSV <span class="text-red-500">*</span></label>
                    <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('file') border-red-400 @enderror">
                    @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Columns: question_text, option_a, option_b, option_c, option_d, correct_answer (a/b/c/d).</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.competitions.show', $competition) }}"
               class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                Upload Paper
            </button>
        </div>
    </form>
</div>
@endsection
