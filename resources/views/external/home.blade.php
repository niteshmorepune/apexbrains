@extends('layouts.external')
@section('title', 'Home')
@section('content')
<div class="p-4">
    <div class="bg-white rounded-2xl p-5 border border-border shadow-sm mb-4">
        <p class="text-lg font-semibold text-gray-900">Welcome, {{ auth()->user()->name }}!</p>
        <p class="text-sm text-gray-500 mt-1">Competition participant</p>
        <div class="mt-3 flex gap-3">
            <a href="{{ route('external.practice.index') }}"
               class="flex-1 bg-fran text-white text-center rounded-xl py-2.5 text-sm font-semibold">
                Practice Papers ({{ $totalPapers }})
            </a>
            <a href="{{ route('external.competitions.index') }}"
               class="flex-1 border border-fran text-fran text-center rounded-xl py-2.5 text-sm font-semibold">
                My Competitions
            </a>
        </div>
    </div>
</div>
@endsection
