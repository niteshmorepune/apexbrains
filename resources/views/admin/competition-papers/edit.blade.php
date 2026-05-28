@extends('layouts.admin')
@section('title', 'Edit Paper #' . $paper->paper_number)
@section('page-title', 'Edit Practice Paper #' . $paper->paper_number)

@section('content')
<div class="max-w-2xl">
    <form id="paper-edit-form" method="POST" action="{{ route('admin.competition-papers.update', $paper) }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-border p-6 mb-4">
            <h2 class="text-sm font-bold text-admin mb-4">Paper Details</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $paper->title) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="2"
                              class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('description', $paper->description) }}</textarea>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Total Questions</label>
                        <input type="number" name="total_questions" value="{{ old('total_questions', $paper->total_questions) }}" min="1" max="200" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (min)</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $paper->duration_minutes) }}" min="1" max="180" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Difficulty</label>
                        <select name="difficulty" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            @foreach(['easy', 'medium', 'hard'] as $d)
                                <option value="{{ $d }}" @selected(old('difficulty', $paper->difficulty) === $d)>{{ ucfirst($d) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                               {{ old('is_active', $paper->is_active) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-fran after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Active (visible to students)</span>
                </label>
            </div>
        </div>

    </form>

    <div class="flex items-center gap-3 mt-4">
        <a href="{{ route('admin.competition-papers.index') }}"
           class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
            Cancel
        </a>
        <button type="submit" form="paper-edit-form"
                class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            Save Changes
        </button>
        <form method="POST" action="{{ route('admin.competition-papers.destroy', $paper) }}" class="ml-auto"
              onsubmit="return confirm('Delete Paper #{{ $paper->paper_number }} permanently?')">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2.5 text-red-500 text-sm hover:underline">Delete</button>
        </form>
    </div>
</div>
@endsection
