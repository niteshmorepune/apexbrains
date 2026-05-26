@extends('layouts.admin')
@section('title', 'Level Manager')
@section('page-title', 'Level Manager — Abacus Levels 1–14')

@section('page-actions')
    <a href="{{ route('admin.levels.create') }}"
       class="inline-flex items-center gap-2 bg-fran text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-fran-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Level
    </a>
@endsection

@section('content')

@php
$levelColors = [
    1=>'#87CEEB', 2=>'#2ECC71', 3=>'#00BCD4', 4=>'#FFD54F', 5=>'#F5A623',
    6=>'#FF69B4', 7=>'#D42B2B', 8=>'#9C27B0', 9=>'#1A73E8', 10=>'#00897B',
    11=>'#FF6F00', 12=>'#AD1457', 13=>'#283593', 14=>'#212121',
];
@endphp

<div class="grid grid-cols-4 gap-4">
    @forelse($levels as $level)
        @php $color = $levelColors[$level->number] ?? '#1A73E8'; @endphp
        <div class="bg-white rounded-2xl border border-border p-5 hover:shadow-md transition-shadow">

            {{-- Level badge --}}
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm"
                     style="background-color: {{ $color }}">
                    L{{ $level->number }}
                </div>
                @if(!$level->is_active)
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                @endif
            </div>

            <h3 class="font-semibold text-admin text-sm mb-1">{{ $level->title }}</h3>
            <p class="text-xs text-gray-400 mb-3">{{ number_format($level->students_count) }} students</p>

            @if($level->description)
                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $level->description }}</p>
            @endif

            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                <span>₹{{ number_format($level->fee_per_month) }}/mo</span>
            </div>

            {{-- Progress bar: students relative to max --}}
            @php $maxStudents = $levels->max('students_count') ?: 1; @endphp
            <div class="h-1.5 bg-bg-mid rounded-full mb-4">
                <div class="h-1.5 rounded-full transition-all"
                     style="width: {{ ($level->students_count / $maxStudents) * 100 }}%; background-color: {{ $color }}">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.levels.edit', $level) }}"
                   class="flex-1 text-center text-xs font-medium text-fran border border-fran rounded-lg py-1.5 hover:bg-fran-light transition-colors">
                    Edit
                </a>
                <a href="{{ route('admin.levels.show', $level) }}"
                   class="flex-1 text-center text-xs font-medium text-gray-600 border border-border rounded-lg py-1.5 hover:bg-bg-light transition-colors">
                    View
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-4 bg-white rounded-2xl border border-border p-12 text-center text-gray-400">
            No levels configured yet.
            <a href="{{ route('admin.levels.create') }}" class="text-fran hover:underline ml-1">Add Level 1 →</a>
        </div>
    @endforelse
</div>

@endsection
