@extends('layouts.external')
@section('title', 'Home')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', trim(auth()->user()->name))[0];
@endphp

<div class="px-4 pt-5 pb-4 space-y-4">

    {{-- Brand row + avatar --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
            @if(!empty($appSettings['logo_path']))
                <img src="{{ Storage::url($appSettings['logo_path']) }}" alt="{{ $appSettings['app_name'] ?? 'Apex Brains' }}" class="h-8 w-auto">
            @else
                <span class="text-xl leading-none">🧮</span>
                <span class="text-base font-extrabold tracking-tight leading-none">
                    <span class="text-logo-red">A</span><span class="text-fran">p</span><span class="text-[#34A853]">e</span><span class="text-logo-amber">x</span><span class="text-logo-red"> B</span><span class="text-fran">r</span><span class="text-[#34A853]">a</span><span class="text-logo-amber">i</span><span class="text-logo-red">n</span><span class="text-fran">s</span>
                </span>
            @endif
        </div>
        <a href="{{ route('external.profile') }}" class="w-9 h-9 rounded-full bg-fran text-white flex items-center justify-center text-sm font-bold">
            {{ strtoupper(substr($firstName, 0, 1)) . strtoupper(substr(explode(' ', trim(auth()->user()->name))[1] ?? '', 0, 1)) }}
        </a>
    </div>

    {{-- Greeting --}}
    <div>
        <h1 class="text-2xl font-black text-gray-900">{{ $greeting }}, {{ $firstName }}!</h1>
        <p class="text-sm text-gray-400 mt-0.5">Practice, Improve, Achieve</p>
    </div>

    {{-- Quick Actions --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Quick Actions</p>
        <div class="grid grid-cols-2 gap-3">
            @foreach([
                ['route' => 'external.practice.hub',        'emoji' => '🎯', 'label' => 'Practice',            'bg' => 'bg-stu-light',  'border' => '#2ECC71'],
                ['route' => 'external.practice.index',      'emoji' => '📝', 'label' => 'Practice Papers',     'bg' => 'bg-fran-light', 'border' => '#1A73E8'],
                ['route' => 'external.competitions.index',  'emoji' => '🏆', 'label' => 'Competitions',        'bg' => 'bg-amber-50',   'border' => '#F5A623'],
                ['route' => 'external.certificates.index',  'emoji' => '🎓', 'label' => 'Results & Certificate','bg' => 'bg-pink-50',    'border' => '#D42B2B'],
            ] as $a)
                <a href="{{ route($a['route']) }}" class="bg-white rounded-2xl border border-border overflow-hidden flex items-stretch">
                    <span class="w-1.5 flex-shrink-0" style="background-color: {{ $a['border'] }}"></span>
                    <div class="flex items-center gap-3 p-4 flex-1 min-w-0">
                        <span class="w-10 h-10 rounded-xl {{ $a['bg'] }} flex items-center justify-center text-xl flex-shrink-0">{{ $a['emoji'] }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-800 leading-tight">{{ $a['label'] }}</p>
                            <p class="text-[11px] text-gray-400">Tap to open</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Recent Activity --}}
    @if($recentAttempts->isNotEmpty())
        <div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Recent Activity</p>
                <a href="{{ route('external.results') }}" class="text-xs font-semibold text-fran">View All</a>
            </div>
            <div class="bg-white rounded-2xl border border-border divide-y divide-border overflow-hidden">
                @foreach($recentAttempts as $att)
                    <div class="px-4 py-3 flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-fran flex-shrink-0"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $att->paper?->title ?? 'Practice Paper' }}</p>
                            <p class="text-xs text-gray-400">{{ $att->submitted_at?->diffForHumans() }}</p>
                        </div>
                        <span class="text-sm font-bold text-fran">{{ number_format($att->percentage, 0) }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
