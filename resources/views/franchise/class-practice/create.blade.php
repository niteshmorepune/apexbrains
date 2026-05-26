@extends('layouts.franchise')
@section('title', 'New Class Practice Session')
@section('page-title', 'New Class Practice Session')

@section('page-actions')
    <a href="{{ route('franchise.class-practice.index') }}"
       class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">
        ← Back
    </a>
@endsection

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-border p-6">
        <h2 class="text-sm font-bold text-fran mb-5">Session Setup</h2>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('franchise.class-practice.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Session Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="100"
                       placeholder="e.g. Friday Level 3 Practice"
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Level <span class="text-red-500">*</span></label>
                    <select name="level_id" required
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <option value="">Select Level</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>
                                Level {{ $level->number }}@if($level->title) — {{ $level->title }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Batch (optional)</label>
                    <select name="batch_id"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <option value="">No specific batch</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" @selected(old('batch_id') == $batch->id)>
                                {{ $batch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Question Category <span class="text-red-500">*</span></label>
                <select name="question_category" required
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    <option value="mcq"         @selected(old('question_category') === 'mcq')>MCQ</option>
                    <option value="abacus"       @selected(old('question_category') === 'abacus')>Abacus</option>
                    <option value="mental_math"  @selected(old('question_category') === 'mental_math')>Mental Math</option>
                    <option value="mixed"        @selected(old('question_category') === 'mixed')>Mixed (all types)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Number of Questions <span class="text-red-500">*</span></label>
                    <input type="number" name="total_questions" value="{{ old('total_questions', 10) }}"
                           min="1" max="50" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    <p class="text-xs text-gray-400 mt-1">Max 50</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Time Per Question (seconds) <span class="text-red-500">*</span></label>
                    <input type="number" name="time_per_question_seconds" value="{{ old('time_per_question_seconds', 30) }}"
                           min="5" max="300" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    <p class="text-xs text-gray-400 mt-1">5–300 seconds</p>
                </div>
            </div>

            <div class="bg-blue-50 rounded-xl p-4 text-sm text-fran">
                Questions will be randomly selected from the approved question bank for the chosen level and category.
            </div>

            <button type="submit"
                    class="w-full py-3 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                Create Session
            </button>
        </form>
    </div>
</div>

@endsection
