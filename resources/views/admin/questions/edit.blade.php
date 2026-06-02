@extends('layouts.admin')
@section('title', 'Edit Question')
@section('page-title', 'Question Editor')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main form --}}
    <div class="col-span-2">
        <form id="qedit-form" method="POST" action="{{ route('admin.questions.update', $question) }}">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Question Content</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Question Text <span class="text-red-500">*</span></label>
                    <textarea name="question_text" rows="4" required
                              class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none @error('question_text') border-red-400 @enderror">{{ old('question_text', $question->question_text) }}</textarea>
                    @error('question_text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Level <span class="text-red-500">*</span></label>
                        <select name="level_id" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(old('level_id', $question->level_id) == $level->id)>Level {{ $level->number }} — {{ $level->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                        <select name="type" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="mcq" @selected(old('type', $question->type) === 'mcq')>MCQ</option>
                            <option value="audio" @selected(old('type', $question->type) === 'audio')>Audio</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Difficulty</label>
                        <select name="difficulty" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="easy" @selected(old('difficulty', $question->difficulty) === 'easy')>Easy</option>
                            <option value="medium" @selected(old('difficulty', $question->difficulty) === 'medium')>Medium</option>
                            <option value="hard" @selected(old('difficulty', $question->difficulty) === 'hard')>Hard</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category / Tag</label>
                    <input type="text" name="question_category" value="{{ old('question_category', $question->question_category) }}"
                           placeholder="e.g. Addition, Mental Math"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>

            {{-- MCQ Options --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Answer Options (MCQ)</h2>
                <div class="space-y-3">
                    @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label)
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-admin flex items-center justify-center text-xs font-bold text-white flex-shrink-0">{{ $label }}</span>
                            <input type="text" name="option_{{ $key }}" value="{{ old('option_' . $key, $question->{'option_' . $key}) }}"
                                   placeholder="Option {{ $label }}"
                                   class="flex-1 border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                <input type="radio" name="correct_answer" value="{{ $label }}"
                                       {{ old('correct_answer', $question->correct_answer) === $label ? 'checked' : '' }}
                                       class="accent-stu">
                                Correct
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Audio --}}
            @if($question->audio_file_path)
                <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                    <h2 class="text-sm font-bold text-admin mb-3">Audio Preview</h2>
                    <p class="text-xs text-gray-500 font-mono bg-bg-light rounded-lg p-2">{{ $question->audio_file_path }}</p>
                </div>
            @endif

        </form>

        {{-- Action row — Delete is its OWN form; never nest forms (a nested
             DELETE _method would hijack Save and delete the record). --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.questions.index') }}"
               class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">Cancel</a>
            <button type="submit" form="qedit-form"
                    class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                Save Changes
            </button>
            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" class="ml-auto"
                  onsubmit="return confirm('Delete this question permanently?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2.5 text-red-500 text-sm hover:underline">Delete Question</button>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-admin mb-3">Question Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">ID</dt>
                    <dd class="font-medium text-admin">#{{ $question->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $question->status === 'approved' ? 'bg-stu-light text-stu-dark' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($question->status) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Created</dt>
                    <dd class="text-gray-600">{{ $question->created_at->format('d M Y') }}</dd>
                </div>
                @if($question->approved_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Approved</dt>
                        <dd class="text-gray-600">{{ $question->approved_at->format('d M Y') }}</dd>
                    </div>
                @endif
                @if($question->source_pdf)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Source PDF</dt>
                        <dd class="text-gray-600 text-xs max-w-28 truncate">{{ $question->source_pdf }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        @if($question->status === 'pending')
            <div class="bg-yellow-50 rounded-2xl border border-yellow-200 p-5">
                <h3 class="text-sm font-bold text-yellow-800 mb-3">Review Actions</h3>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.questions.approve', $question) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-stu text-white rounded-xl text-sm font-medium">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.questions.reject', $question) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2 border border-red-300 text-red-600 rounded-xl text-sm font-medium">Reject</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
