@extends('layouts.admin')
@section('title', $level->title)
@section('page-title', $level->title)

@section('page-actions')
    <a href="{{ route('admin.levels.edit', $level) }}"
       class="px-4 py-2 bg-fran text-white text-sm font-semibold rounded-xl hover:bg-fran-dark transition-colors">
        Edit Syllabus
    </a>
@endsection

@section('content')

@php
$levelColors = [
    1=>'#87CEEB', 2=>'#2ECC71', 3=>'#00BCD4', 4=>'#FFD54F', 5=>'#F5A623',
    6=>'#FF69B4', 7=>'#D42B2B', 8=>'#9C27B0', 9=>'#1A73E8', 10=>'#00897B',
    11=>'#FF6F00', 12=>'#AD1457', 13=>'#283593', 14=>'#212121',
];
$color = $levelColors[$level->number] ?? '#1A73E8';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-border p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $color }}"></div>
                <div>
                    <h2 class="font-bold text-admin text-base">{{ $level->title }}</h2>
                    <p class="text-sm text-gray-400">{{ number_format($level->students_count) }} students enrolled</p>
                </div>
            </div>
            @if($level->description)
                <p class="text-sm text-gray-600">{{ $level->description }}</p>
            @endif
        </div>

        @if($level->learning_objectives)
            <div class="bg-white rounded-2xl border border-border p-6">
                <h3 class="text-sm font-bold text-admin mb-3">Learning Objectives</h3>
                <ol class="space-y-2">
                    @foreach($level->learning_objectives as $i => $obj)
                        <li class="flex items-start gap-3 text-sm text-gray-700">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5"
                                  style="background-color: {{ $color }}">{{ $i + 1 }}</span>
                            {{ $obj }}
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-border p-6">
            <h3 class="text-sm font-bold text-admin mb-3">Assigned Books</h3>
            @if($level->books->isNotEmpty())
                <div class="space-y-2">
                    @foreach($level->books as $book)
                        <div class="flex items-center gap-3 p-4 bg-bg-light rounded-xl">
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-red-600">{{ strtoupper($book->file_type ?? 'PDF') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-admin truncate">{{ $book->title }}</p>
                                <p class="text-xs text-gray-400">{{ $book->formatted_size }}</p>
                            </div>
                            <a href="{{ route('admin.resources.download', $book) }}"
                               class="text-xs font-medium text-fran hover:underline flex-shrink-0">Download</a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-center gap-3 p-4 bg-bg-light rounded-xl">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-bold text-gray-400">PDF</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-400">No books assigned yet</p>
                        <p class="text-xs text-gray-300">Assign from the Resource Library via Edit Syllabus.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-admin mb-3">Details</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Monthly Fee</span>
                    <span class="font-semibold">₹{{ number_format($level->fee_per_month) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Students</span>
                    <span class="font-semibold">{{ number_format($level->students_count) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Status</span>
                    <span class="{{ $level->is_active ? 'text-stu font-semibold' : 'text-gray-400' }}">
                        {{ $level->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <a href="{{ route('admin.levels.edit', $level) }}"
               class="w-full text-center py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                Edit Syllabus
            </a>
            <a href="{{ route('admin.levels.index') }}"
               class="w-full text-center py-2.5 border border-border text-gray-600 rounded-xl text-sm hover:bg-bg-light transition-colors">
                ← Back to Levels
            </a>
        </div>
    </div>
</div>
@endsection
