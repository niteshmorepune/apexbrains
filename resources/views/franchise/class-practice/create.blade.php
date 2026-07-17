@extends('layouts.franchise')
@section('title', 'Class Practice')
@section('page-title', 'Class Practice')

@section('content')

<div class="max-w-3xl mx-auto"
     x-data="{
        accessByLevel: @js($accessByLevel),
        levelId: '{{ old('level_id') }}',
        categoryId: '{{ old('category_id') }}',
        get categories() { return this.accessByLevel[this.levelId] || []; },
        get types() {
            const cat = this.categories.find(c => c.id == this.categoryId);
            return cat ? cat.types : [];
        },
     }">
    <div class="bg-white rounded-2xl border border-border shadow-sm p-8 sm:p-10">

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('franchise.class-practice.store') }}" class="space-y-8">
            @csrf

            {{-- Select Level --}}
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Select Level</label>
                <div class="relative">
                    <select name="level_id" x-model="levelId" @change="categoryId = ''" required
                            class="w-full appearance-none border border-border rounded-xl px-4 py-3.5 text-base text-gray-700 bg-bg-light focus:outline-none focus:ring-2 focus:ring-fran focus:bg-white">
                        <option value="">Choose a level…</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>
                                @if($level->title){{ $level->title }} (L{{ $level->number }})@else Level {{ $level->number }}@endif
                            </option>
                        @endforeach
                    </select>
                    <svg class="w-5 h-5 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Category / Type (accessible for the selected level) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="levelId">
                <div>
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Category</label>
                    <select name="category_id" x-model="categoryId" required
                            class="w-full appearance-none border border-border rounded-xl px-4 py-3.5 text-base text-gray-700 bg-bg-light focus:outline-none focus:ring-2 focus:ring-fran focus:bg-white">
                        <option value="">Choose a category…</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-lg font-semibold text-gray-800 mb-3">Type</label>
                    <select name="type_id" required
                            class="w-full appearance-none border border-border rounded-xl px-4 py-3.5 text-base text-gray-700 bg-bg-light focus:outline-none focus:ring-2 focus:ring-fran focus:bg-white">
                        <option value="">Choose a type…</option>
                        <template x-for="t in types" :key="t.id">
                            <option :value="t.id" x-text="t.name" :selected="t.id == {{ old('type_id', 0) }}"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- Time per number (flash speed) --}}
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Time per number</label>
                <div class="relative">
                    <select name="time_per_question_seconds" required
                            class="w-full appearance-none border border-border rounded-xl px-4 py-3.5 text-base text-gray-700 bg-bg-light focus:outline-none focus:ring-2 focus:ring-fran focus:bg-white">
                        @foreach(['3' => '3 Sec', '2.5' => '2.5 Sec', '2' => '2 Sec', '1.5' => '1.5 Sec', '1' => '1 Sec', '0.5' => '0.5 Sec'] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('time_per_question_seconds', '2') == $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <svg class="w-5 h-5 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Number of Questions --}}
            <div>
                <label class="block text-lg font-semibold text-gray-800 mb-3">Number of Questions</label>
                <div class="relative">
                    <select name="total_questions" required
                            class="w-full appearance-none border border-border rounded-xl px-4 py-3.5 text-base text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-fran">
                        @foreach(['10', '20', '30'] as $opt)
                            <option value="{{ $opt }}" @selected(old('total_questions', '10') == $opt)>{{ $opt }} Questions</option>
                        @endforeach
                    </select>
                    <svg class="w-5 h-5 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <hr class="border-border">

            {{-- Audio Dictation toggle --}}
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-semibold text-gray-800">Audio Dictation</p>
                    <p class="text-sm text-gray-500 mt-0.5">Play voice for each number automatically</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="audio_dictation" value="0">
                    <input type="checkbox" name="audio_dictation" value="1" class="sr-only peer"
                           {{ old('audio_dictation', '1') ? 'checked' : '' }}>
                    <div class="w-12 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-6 peer-checked:bg-fran after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
            </div>

            <hr class="border-border">

            <button type="submit"
                    class="w-full py-4 bg-fran text-white rounded-2xl text-base font-semibold hover:bg-fran-dark transition-colors">
                Next Question
            </button>
        </form>
    </div>
</div>

@endsection
