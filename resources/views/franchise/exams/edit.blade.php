@extends('layouts.franchise')
@section('title', 'Edit Exam')
@section('page-title', 'Edit Exam')

@section('page-actions')
    <a href="{{ route('franchise.exams.show', $exam) }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Back
    </a>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="bg-white rounded-2xl border border-border p-6">
        <h2 class="text-sm font-bold text-fran mb-5">Edit Exam Details</h2>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('franchise.exams.update', $exam) }}" class="space-y-5">
            @csrf
            @method('PUT')

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

            <div class="grid grid-cols-2 gap-4">
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
                    <input type="number" name="total_questions" value="{{ old('total_questions', $exam->total_questions) }}"
                           min="1" max="100" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}"
                           min="5" max="180" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Pass % <span class="text-red-500">*</span></label>
                    <input type="number" name="pass_percentage" value="{{ old('pass_percentage', $exam->pass_percentage) }}"
                           min="1" max="100" step="0.01" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Max Attempts</label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', $exam->max_attempts) }}"
                           min="1" placeholder="Unlimited"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Scheduled Date/Time</label>
                    <input type="datetime-local" name="scheduled_at"
                           value="{{ old('scheduled_at', $exam->scheduled_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Expires At</label>
                    <input type="datetime-local" name="expires_at"
                           value="{{ old('expires_at', $exam->expires_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div class="flex items-end pb-0.5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $exam->is_active))
                               class="w-4 h-4 rounded accent-fran">
                        <span class="text-sm font-medium text-gray-700">Active (visible to students)</span>
                    </label>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                Save Changes
            </button>
        </form>
    </div>

    {{-- Danger zone --}}
    @if(!$exam->attempts()->exists())
        <div class="bg-white rounded-2xl border border-red-200 p-5">
            <h3 class="text-sm font-bold text-red-600 mb-2">Danger Zone</h3>
            <form method="POST" action="{{ route('franchise.exams.destroy', $exam) }}"
                  onsubmit="return confirm('Delete this exam? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-xl text-sm font-semibold hover:bg-red-600">
                    Delete Exam
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
