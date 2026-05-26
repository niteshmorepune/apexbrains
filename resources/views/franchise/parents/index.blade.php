@extends('layouts.franchise')
@section('title', 'Parent Directory')
@section('page-title', 'Parent Contact Directory')

@section('content')

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-border p-4 mb-4">
    <form method="GET" action="{{ route('franchise.parents.index') }}" class="flex items-center gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search parent or student name..."
               class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran flex-1 min-w-48">
        <select name="level" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            <option value="">All Levels</option>
            <option value="1-4" @selected(request('level') === '1-4')>L1–4</option>
            <option value="5-8" @selected(request('level') === '5-8')>L5–8</option>
            <option value="9-14" @selected(request('level') === '9-14')>L9+</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Search</button>
        @if(request('search') || request('level'))
            <a href="{{ route('franchise.parents.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
    </form>
</div>

{{-- Contact cards grid --}}
<div class="grid grid-cols-2 gap-4">
    @forelse($parents as $parent)
        <div class="bg-white rounded-2xl border border-border p-5 hover:border-fran transition-colors">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-bg-mid flex items-center justify-center text-gray-600 font-bold flex-shrink-0">
                    {{ strtoupper(substr($parent->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800">{{ $parent->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Parent of
                        <a href="{{ route('franchise.students.show', $parent->student) }}" class="text-fran hover:underline font-medium">
                            {{ $parent->student?->full_name }}
                        </a>
                        @if($parent->student?->currentLevel)
                            · <span class="text-xs bg-blue-50 text-fran px-1.5 py-0.5 rounded-full">L{{ $parent->student->currentLevel->number }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="mt-3 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 w-16">Phone</span>
                    <a href="tel:{{ $parent->phone }}" class="text-sm text-fran font-medium hover:underline">{{ $parent->phone }}</a>
                </div>
                @if($parent->whatsapp)
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 w-16">WhatsApp</span>
                        <a href="https://wa.me/91{{ preg_replace('/\D/', '', $parent->whatsapp) }}"
                           target="_blank"
                           class="text-sm text-stu font-medium hover:underline">{{ $parent->whatsapp }}</a>
                    </div>
                @endif
                @if($parent->email)
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 w-16">Email</span>
                        <a href="mailto:{{ $parent->email }}" class="text-sm text-gray-600 hover:underline truncate">{{ $parent->email }}</a>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-2 bg-white rounded-2xl border border-border p-12 text-center text-gray-400">
            No parents found. Register students to see their parent contacts here.
        </div>
    @endforelse
</div>

@if($parents->hasPages())
    <div class="mt-4 flex justify-center">
        {{ $parents->links('pagination::tailwind') }}
    </div>
@endif

@endsection
