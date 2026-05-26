@extends('layouts.admin')
@section('title', 'Edit ' . $level->title)
@section('page-title', 'Level ' . $level->number . ' — Edit Syllabus')

@section('content')

<div class="grid grid-cols-3 gap-6">
    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.levels.update', $level) }}">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-4">Level Details</h2>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Level Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $level->title) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('title') border-red-400 @enderror">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Monthly Fee (₹) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="fee_per_month" value="{{ old('fee_per_month', $level->fee_per_month) }}"
                               min="0" step="50" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('fee_per_month') border-red-400 @enderror">
                    </div>

                    <div class="flex items-center gap-3 pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                   {{ old('is_active', $level->is_active) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-fran rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-fran after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('description', $level->description) }}</textarea>
                </div>
            </div>

            {{-- Learning Objectives --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-6"
                 x-data="{ objectives: {{ json_encode(old('learning_objectives', $level->learning_objectives ?? [])) }} }">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-admin">Learning Objectives</h2>
                    <button type="button" @click="objectives.push('')"
                            class="text-xs text-fran hover:underline font-medium">+ Add</button>
                </div>

                <template x-for="(obj, idx) in objectives" :key="idx">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs text-gray-400 w-5 text-right flex-shrink-0" x-text="idx + 1 + '.'"></span>
                        <input type="text" :name="'learning_objectives[' + idx + ']'"
                               x-model="objectives[idx]" placeholder="e.g. Master 2-digit addition in under 10 seconds"
                               class="flex-1 border border-border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <button type="button" @click="objectives.splice(idx, 1)"
                                class="text-gray-400 hover:text-red-500 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <template x-if="objectives.length === 0">
                    <p class="text-sm text-gray-400 text-center py-4">No objectives yet. Click + Add to start.</p>
                </template>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.levels.index') }}"
                   class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Level info sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            @php
            $levelColors = [
                1=>'#87CEEB', 2=>'#2ECC71', 3=>'#00BCD4', 4=>'#FFD54F', 5=>'#F5A623',
                6=>'#FF69B4', 7=>'#D42B2B', 8=>'#9C27B0', 9=>'#1A73E8', 10=>'#00897B',
                11=>'#FF6F00', 12=>'#AD1457', 13=>'#283593', 14=>'#212121',
            ];
            $color = $levelColors[$level->number] ?? '#1A73E8';
            @endphp
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg mb-3"
                 style="background-color: {{ $color }}">
                L{{ $level->number }}
            </div>
            <h3 class="font-semibold text-admin mb-3">{{ $level->title }}</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Level #</span>
                    <span>{{ $level->number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Sort order</span>
                    <span>{{ $level->sort_order }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Status</span>
                    <span class="{{ $level->is_active ? 'text-stu' : 'text-gray-400' }} font-medium">
                        {{ $level->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
