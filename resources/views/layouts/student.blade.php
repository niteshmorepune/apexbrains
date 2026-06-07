<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') — Apex Brains</title>
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
        <div class="px-4 pt-3">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif
    @if(session('error'))
        <div class="px-4 pt-3">
            <x-alert type="error" :message="session('error')" />
        </div>
    @endif

    {{-- Page content (each screen renders its own <x-student-header> where needed) --}}
    <main class="flex-1 overflow-y-auto pb-24">
        @yield('content')
    </main>

    {{-- Bottom Navigation — emoji icons, green active + indicator (matches Figma) --}}
    @php
        $navItems = [
            ['route' => 'student.home',          'match' => 'student.home',          'emoji' => '🏠', 'label' => 'Home'],
            ['route' => 'student.practice.index','match' => 'student.practice.*',     'emoji' => '📚', 'label' => 'Practice'],
            ['route' => 'student.exams.index',   'match' => 'student.exams.*',        'emoji' => '📝', 'label' => 'Exams'],
            ['route' => 'student.results',        'match' => 'student.results',        'emoji' => '🏆', 'label' => 'Results'],
            ['route' => 'student.profile',        'match' => 'student.profile',        'emoji' => '👤', 'label' => 'Profile'],
        ];
        // Competitions live under the Exams tab; certificates under the Results tab.
        $examActive    = request()->routeIs('student.exams.*') || request()->routeIs('student.competitions.*');
        $resultsActive = request()->routeIs('student.results') || request()->routeIs('student.certificates.*');
    @endphp
    <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-sm md:max-w-md bg-white border-t border-border z-30 safe-bottom">
        <div class="flex justify-around items-stretch h-16">
            @foreach($navItems as $item)
                @php
                    $active = match($item['route']) {
                        'student.exams.index' => $examActive,
                        'student.results'     => $resultsActive,
                        default               => request()->routeIs($item['match']),
                    };
                @endphp
                <a href="{{ route($item['route']) }}"
                   class="relative flex flex-col items-center justify-center gap-0.5 flex-1">
                    @if($active)
                        <span class="absolute top-0 h-1 w-8 bg-stu rounded-b-full"></span>
                    @endif
                    <span class="text-xl leading-none {{ $active ? '' : 'opacity-50 grayscale' }}">{{ $item['emoji'] }}</span>
                    <span class="text-[10px] font-medium {{ $active ? 'text-stu font-bold' : 'text-gray-400' }}">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
</div>
@stack('scripts')
</body>
</html>
