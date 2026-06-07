<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') — Apex Brains Competition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { -webkit-tap-highlight-color: transparent; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0.5rem); }
    </style>
    @stack('head')
</head>
<body class="bg-stu-bg md:bg-slate-200 min-h-screen flex flex-col">

{{-- App column: phone-width on mobile, centered framed column on desktop --}}
<div class="max-w-sm md:max-w-md mx-auto w-full flex flex-col min-h-screen relative bg-stu-bg md:shadow-xl">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="px-4 pt-3"><x-alert type="success" :message="session('success')" /></div>
    @endif
    @if(session('error'))
        <div class="px-4 pt-3"><x-alert type="error" :message="session('error')" /></div>
    @endif

    {{-- Page content (each screen renders its own <x-student-header> where needed) --}}
    <main class="flex-1 overflow-y-auto pb-24">
        @yield('content')
    </main>

    {{-- Bottom Navigation — emoji icons, blue active + indicator (external / competition) --}}
    @php
        $extNav = [
            ['route' => 'external.home',              'emoji' => '🏠', 'label' => 'Home',     'active' => request()->routeIs('external.home')],
            ['route' => 'external.practice.index',    'emoji' => '📚', 'label' => 'Practice', 'active' => request()->routeIs('external.practice.*')],
            ['route' => 'external.competitions.index','emoji' => '🏆', 'label' => 'Exams',    'active' => request()->routeIs('external.competitions.*')],
            ['route' => 'external.results',            'emoji' => '📊', 'label' => 'Results',  'active' => request()->routeIs('external.results') || request()->routeIs('external.certificates.*')],
            ['route' => 'external.profile',           'emoji' => '👤', 'label' => 'Profile',  'active' => request()->routeIs('external.profile')],
        ];
    @endphp
    <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-sm md:max-w-md bg-white border-t border-border z-30 safe-bottom">
        <div class="flex justify-around items-stretch h-16">
            @foreach($extNav as $item)
                <a href="{{ route($item['route']) }}" class="relative flex flex-col items-center justify-center gap-0.5 flex-1">
                    @if($item['active'])
                        <span class="absolute top-0 h-1 w-8 bg-fran rounded-b-full"></span>
                    @endif
                    <span class="text-xl leading-none {{ $item['active'] ? '' : 'opacity-50 grayscale' }}">{{ $item['emoji'] }}</span>
                    <span class="text-[10px] font-medium {{ $item['active'] ? 'text-fran font-bold' : 'text-gray-400' }}">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
</div>
@stack('scripts')
</body>
</html>
