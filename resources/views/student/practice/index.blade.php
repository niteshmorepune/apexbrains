@extends('layouts.student')
@section('title', 'Practice')

@section('content')
<div x-data="{
        type: '',
        step: 'category',
        categories: @js($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'types' => $c->types->map(fn($t) => ['id' => $t->id, 'name' => $t->name])])),
        categoryId: null,
        typeId: null,
        get selectedCategory() { return this.categories.find(c => c.id === this.categoryId) || null; },
        get selectedType() {
            const cat = this.selectedCategory;
            return cat ? (cat.types.find(t => t.id === this.typeId) || null) : null;
        },
     }">

    {{-- ===== Step 1: type selector (S34) ===== --}}
    <template x-if="type === ''">
        <div>
            <x-student-header title="Practice" :back="route('student.home')" />
            <div class="px-4 pb-4 space-y-3">
                <button type="button" @click="type = 'exam'; step = 'category'"
                        class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-stu-light flex items-center justify-center text-2xl flex-shrink-0">📝</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Regular Practice</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Evaluate your calculation skills and track your learning progress</p>
                    </div>
                </button>
                <a href="{{ route('student.competitions.practice') }}"
                   class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-2xl flex-shrink-0">🏆</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Competition Practice</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Challenge your abilities and compete with top performers</p>
                    </div>
                </a>
            </div>
        </div>
    </template>

    {{-- ===== Step 2: category / type / count picker ===== --}}
    <template x-if="type === 'exam'">
        <div>
            <div class="px-4 pt-5 pb-3 flex items-center gap-2">
                <button type="button"
                        @click="step === 'category' ? (type = '') : (step === 'type' ? (step = 'category', categoryId = null) : (step = 'type', typeId = null))"
                        class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900">Regular Practice</h1>
            </div>

            <div class="px-4 pb-4 space-y-5">
                @error('type_id')
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ $message }}</div>
                @enderror

                @if($categories->isEmpty())
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                        No practice categories are available for your level yet. Please check with your branch.
                    </div>
                @endif

                {{-- Category step --}}
                <template x-if="step === 'category'">
                    <div class="space-y-3">
                        <p class="text-sm text-gray-500">Choose a category to begin.</p>
                        <template x-for="cat in categories" :key="cat.id">
                            <button type="button" @click="categoryId = cat.id; step = 'type'"
                                    class="w-full bg-white rounded-2xl border border-border p-4 flex items-center justify-between text-left">
                                <p class="font-bold text-gray-800" x-text="cat.name"></p>
                                <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </template>
                    </div>
                </template>

                {{-- Type step --}}
                <template x-if="step === 'type'">
                    <div class="space-y-3">
                        <p class="text-sm text-gray-500">Choose a type.</p>
                        <template x-for="t in (selectedCategory ? selectedCategory.types : [])" :key="t.id">
                            <button type="button" @click="typeId = t.id; step = 'count'"
                                    class="w-full bg-white rounded-2xl border border-border p-4 flex items-center justify-between text-left">
                                <p class="font-bold text-gray-800" x-text="t.name"></p>
                                <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </template>
                    </div>
                </template>

                {{-- Count step — tapping starts the session immediately --}}
                <template x-if="step === 'count'">
                    <div class="space-y-3">
                        <p class="text-sm text-gray-500">Choose number of questions.</p>
                        <form method="POST" action="{{ route('student.practice.start') }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="category_id" :value="categoryId">
                            <input type="hidden" name="type_id" :value="typeId">
                            @foreach([10, 20, 30] as $count)
                                <button type="submit" name="count" value="{{ $count }}"
                                        class="w-full bg-white rounded-2xl border border-border p-4 flex items-center justify-between text-left">
                                    <p class="font-bold text-gray-800">{{ $count }} Questions</p>
                                    <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            @endforeach
                        </form>
                    </div>
                </template>

                {{-- Recent Sessions --}}
                @if($pastSessions->isNotEmpty())
                    <div>
                        <p class="text-sm font-bold text-gray-800 mb-2">Recent Sessions</p>
                        <div class="space-y-2.5">
                            @foreach($pastSessions as $ps)
                                <div class="bg-white rounded-2xl border border-border p-3.5 flex items-center gap-3">
                                    <span class="text-fran">📅</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800">{{ $ps->completed_at?->format('d M, g:i A') ?? 'In progress' }}</p>
                                        <p class="text-xs text-gray-400">{{ $ps->category?->name }} — {{ $ps->questions_correct ?? 0 }}/{{ $ps->total_questions }} correct · {{ number_format($ps->accuracy ?? 0, 0) }}% accuracy</p>
                                    </div>
                                    <span class="text-base font-black text-stu">{{ number_format($ps->accuracy ?? 0, 0) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </template>

</div>
@endsection
