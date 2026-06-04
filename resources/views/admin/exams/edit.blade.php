@extends('layouts.admin')
@section('title', 'Edit Exam')
@section('page-title', 'Edit Exam')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-border p-6">
        <h2 class="text-sm font-bold text-admin mb-5">Exam Details</h2>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="exam-edit-form" method="POST" action="{{ route('admin.exams.update', $exam) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $exam->title) }}" required maxlength="150"
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="2" maxlength="500"
                          class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('description', $exam->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Level <span class="text-red-500">*</span></label>
                    <select name="level_id" required
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('level_id', $exam->level_id) == $level->id)>
                                Level {{ $level->number }}@if($level->title) — {{ $level->title }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Questions <span class="text-red-500">*</span></label>
                    <input type="number" name="total_questions" value="{{ old('total_questions', $exam->total_questions) }}" min="1" max="100" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="5" max="180" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Pass % <span class="text-red-500">*</span></label>
                    <input type="number" name="pass_percentage" value="{{ old('pass_percentage', $exam->pass_percentage) }}" min="1" max="100" step="0.01" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Max Attempts</label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', $exam->max_attempts) }}" min="1" placeholder="Unlimited"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Scheduled Date/Time</label>
                    <input type="datetime-local" name="scheduled_at"
                           value="{{ old('scheduled_at', $exam->scheduled_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Expires At</label>
                <input type="datetime-local" name="expires_at"
                       value="{{ old('expires_at', $exam->expires_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <div class="relative">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $exam->is_active) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-fran after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </div>
                <span class="text-sm font-medium text-gray-700">Active (visible to students)</span>
            </label>
        </form>

        <div class="flex items-center gap-3 mt-5">
            <a href="{{ route('admin.exams.index') }}"
               class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">Cancel</a>
            <button type="submit" form="exam-edit-form"
                    class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                Save Changes
            </button>
            <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}" class="ml-auto"
                  onsubmit="return confirm('Delete this exam permanently?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2.5 text-red-500 text-sm hover:underline">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
