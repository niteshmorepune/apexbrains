@extends('layouts.admin')
@section('title', 'Edit Question — Competition Bank')
@section('page-title', 'Competition Question Editor')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
     x-data="{
        categories: @js($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'types' => $c->types->map(fn($t) => ['id' => $t->id, 'name' => $t->name])])),
        categoryId: '{{ old('category_id', $question->category_id) }}',
     }">
    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.competition-questions.update', $question) }}">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Question Content</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Question Text <span class="text-red-500">*</span></label>
                    <textarea name="question_text" rows="4" required
                              class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('question_text', $question->question_text) }}</textarea>
                    @error('question_text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" x-model="categoryId" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                        <select name="type_id" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <template x-for="t in (categories.find(c => c.id == categoryId)?.types || [])" :key="t.id">
                                <option :value="t.id" x-text="t.name" :selected="t.id == {{ old('type_id', $question->type_id) }}"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Difficulty <span class="text-red-500">*</span></label>
                        <select name="difficulty" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="easy" @selected(old('difficulty', $question->difficulty) === 'easy')>Easy</option>
                            <option value="medium" @selected(old('difficulty', $question->difficulty) === 'medium')>Medium</option>
                            <option value="hard" @selected(old('difficulty', $question->difficulty) === 'hard')>Hard</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Answer Options</h2>
                <div class="space-y-3">
                    @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label)
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-admin flex items-center justify-center text-xs font-bold text-white flex-shrink-0">{{ $label }}</span>
                            <input type="text" name="option_{{ $key }}" value="{{ old('option_' . $key, $question->{'option_' . $key}) }}"
                                   placeholder="Option {{ $label }}"
                                   class="flex-1 border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                <input type="radio" name="correct_answer" value="{{ $label }}"
                                       {{ old('correct_answer', strtoupper($question->correct_answer)) === $label ? 'checked' : '' }} class="accent-fran">
                                Correct
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('correct_answer')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.competition-questions.index') }}"
                   class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">Cancel</a>
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
