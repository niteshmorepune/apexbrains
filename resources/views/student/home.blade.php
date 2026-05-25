@extends('layouts.student')
@section('title', 'Home')
@section('content')
<div class="p-4">
    <div class="bg-white rounded-2xl p-5 border border-border shadow-sm mb-4">
        <p class="text-lg font-semibold text-gray-900">Welcome, {{ auth()->user()->name }}!</p>
        @if($student && $student->currentLevel)
            <div class="mt-2">
                <x-level-badge :level="$student->currentLevel" />
            </div>
        @endif
    </div>
</div>
@endsection
