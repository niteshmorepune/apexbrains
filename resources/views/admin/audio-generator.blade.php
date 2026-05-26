@extends('layouts.admin')
@section('title', 'Audio Question Generator')
@section('page-title', 'Audio Question Generator')

@section('page-actions')
    <a href="{{ route('admin.questions.index') }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
        ← Question Bank
    </a>
@endsection

@section('content')

<div class="grid grid-cols-3 gap-6">

    {{-- Generator panel --}}
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-border p-6">
            <h2 class="text-sm font-bold text-admin mb-5">Generate New Audio Question</h2>

            <form method="POST" action="{{ route('admin.questions.audio.generate') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Question Text <span class="text-red-500">*</span></label>
                    <textarea name="question_text" rows="4" required placeholder="Type or paste the abacus question..."
                              class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('question_text') }}</textarea>
                    @error('question_text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Level <span class="text-red-500">*</span></label>
                        <select name="level_id" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>Level {{ $level->number }} — {{ $level->title }}</option>
                            @endforeach
                        </select>
                        @error('level_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Difficulty</label>
                        <select name="difficulty" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="easy" @selected(old('difficulty') === 'easy')>Easy</option>
                            <option value="medium" @selected(old('difficulty', 'medium') === 'medium')>Medium</option>
                            <option value="hard" @selected(old('difficulty') === 'hard')>Hard</option>
                        </select>
                    </div>
                </div>

                {{-- Voice settings --}}
                <div class="bg-bg-light rounded-xl p-4 mb-4">
                    <p class="text-xs font-semibold text-gray-600 mb-3">Voice Settings</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1.5">Voice</label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="voice" value="female" checked class="accent-fran">
                                    <span class="text-sm">Female</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="voice" value="male" class="accent-fran">
                                    <span class="text-sm">Male</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1.5">Speed</label>
                            <select name="speed" class="w-full border border-border rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-fran">
                                <option value="slow">Slow (Learning)</option>
                                <option value="normal" selected>Normal</option>
                                <option value="fast">Fast (Competition)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                    <input type="text" name="question_category" value="{{ old('question_category') }}"
                           placeholder="e.g. Addition, Subtraction, Mixed"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>

                <button type="submit"
                        class="w-full py-3 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Generate Audio Question
                </button>
            </form>
        </div>

        {{-- Generated list --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                <h2 class="text-sm font-semibold text-admin">Generated Audio Questions ({{ $audioQuestions->total() }})</h2>
            </div>

            @forelse($audioQuestions as $q)
                <div class="px-5 py-4 border-b border-border last:border-b-0 hover:bg-bg-light flex items-start gap-4">
                    <div class="w-9 h-9 rounded-full bg-stu-light flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-stu" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 3.5a.5.5 0 01.5.5v2a.5.5 0 01-1 0V4a.5.5 0 01.5-.5zM7 7.5A.5.5 0 017.5 7h5a.5.5 0 010 1h-5A.5.5 0 017 7.5zM4 11a.5.5 0 01.5-.5h11a.5.5 0 010 1H4.5A.5.5 0 014 11z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 line-clamp-2">{{ $q->question_text }}</p>
                        <div class="flex items-center gap-3 mt-1">
                            @if($q->level)
                                <span class="text-xs text-fran">L{{ $q->level->number }}</span>
                            @endif
                            <span class="text-xs text-gray-400 capitalize">{{ $q->difficulty }}</span>
                            <span class="text-xs text-gray-400">{{ $q->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="{{ route('admin.questions.edit', $q) }}" class="text-xs text-fran hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.questions.destroy', $q) }}"
                              onsubmit="return confirm('Delete this question?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-gray-400 text-sm">
                    No audio questions yet. Generate your first one above.
                </div>
            @endforelse

            @if($audioQuestions->hasPages())
                <div class="px-5 py-4 border-t border-border">
                    {{ $audioQuestions->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Info sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-admin mb-3">About Audio Questions</h3>
            <p class="text-xs text-gray-500 mb-3">Audio questions are spoken aloud during exams — students hear numbers and answer without seeing them written.</p>
            <ul class="space-y-2 text-xs text-gray-500">
                <li class="flex gap-2"><span class="text-stu font-bold">•</span> Use numbers and simple operators</li>
                <li class="flex gap-2"><span class="text-stu font-bold">•</span> Keep questions under 30 seconds</li>
                <li class="flex gap-2"><span class="text-stu font-bold">•</span> Slow speed recommended for beginners</li>
                <li class="flex gap-2"><span class="text-stu font-bold">•</span> Fast speed suits competition mode</li>
            </ul>
        </div>

        <div class="bg-admin rounded-2xl p-5 text-white">
            <h3 class="text-sm font-bold mb-3">Quick Stats</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-admin-mid rounded-xl p-3 text-center">
                    <p class="text-xl font-bold text-stu">{{ $audioQuestions->total() }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Audio Q's</p>
                </div>
                <div class="bg-admin-mid rounded-xl p-3 text-center">
                    <p class="text-xl font-bold text-logo-amber">{{ $levels->count() }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Levels</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
