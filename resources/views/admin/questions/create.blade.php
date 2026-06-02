@extends('layouts.admin')
@section('title', 'Add Question')
@section('page-title', 'Question Editor')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main form --}}
    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.questions.store') }}">
            @csrf

            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Question Content</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Question Text <span class="text-red-500">*</span></label>
                    <textarea name="question_text" rows="4" required placeholder="Enter the question..."
                              class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none @error('question_text') border-red-400 @enderror">{{ old('question_text') }}</textarea>
                    @error('question_text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Level <span class="text-red-500">*</span></label>
                        <select name="level_id" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>Level {{ $level->number }} — {{ $level->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                        <select name="type" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="mcq" @selected(old('type', 'mcq') === 'mcq')>MCQ</option>
                            <option value="audio" @selected(old('type') === 'audio')>Audio</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Difficulty <span class="text-red-500">*</span></label>
                        <select name="difficulty" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="easy" @selected(old('difficulty') === 'easy')>Easy</option>
                            <option value="medium" @selected(old('difficulty', 'medium') === 'medium')>Medium</option>
                            <option value="hard" @selected(old('difficulty') === 'hard')>Hard</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category / Tag</label>
                    <input type="text" name="question_category" value="{{ old('question_category') }}"
                           placeholder="e.g. Addition, Mental Math, Speed Calculation"
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
                            <input type="text" name="option_{{ $key }}" value="{{ old('option_' . $key) }}"
                                   placeholder="Option {{ $label }}"
                                   class="flex-1 border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                <input type="radio" name="correct_answer" value="{{ $label }}"
                                       {{ old('correct_answer') === $label ? 'checked' : '' }}
                                       class="accent-stu">
                                Correct
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('correct_answer')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.questions.index') }}"
                   class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">Cancel</a>
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Add to Question Bank
                </button>
            </div>
        </form>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-admin mb-3">Tips</h3>
            <ul class="space-y-2 text-xs text-gray-500">
                <li class="flex gap-2"><span class="text-fran font-bold">•</span> Use clear, unambiguous language</li>
                <li class="flex gap-2"><span class="text-fran font-bold">•</span> All 4 MCQ options should be plausible</li>
                <li class="flex gap-2"><span class="text-fran font-bold">•</span> Match difficulty to the level</li>
                <li class="flex gap-2"><span class="text-fran font-bold">•</span> Add category tags to help with filtering</li>
                <li class="flex gap-2"><span class="text-fran font-bold">•</span> Audio questions are auto-generated from text</li>
            </ul>
        </div>

        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-admin mb-3">Quick Add</h3>
            <p class="text-xs text-gray-500 mb-3">Have many questions? Import them all from a CSV or Excel file.</p>
            <a href="{{ route('admin.questions.import') }}"
               class="block text-center px-4 py-2 border border-fran text-fran rounded-xl text-sm font-medium hover:bg-fran hover:text-white transition-colors">
                Bulk Import
            </a>
        </div>
    </div>
</div>

@endsection
