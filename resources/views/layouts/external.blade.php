<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') — Apex Brains Competition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { -webkit-tap-highlight-color: transparent; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0.5rem); }
    </style>
</head>
<body class="bg-bg-light min-h-screen flex flex-col">

<div class="max-w-sm mx-auto w-full flex flex-col min-h-screen md:max-w-full relative">

    {{-- Blue header — EXTERNAL STUDENT (visually distinct from internal green) --}}
    <header class="bg-fran sticky top-0 z-30 flex-shrink-0">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-white flex items-center justify-center">
                    <span class="text-fran font-bold text-xs">AB</span>
                </div>
                <div>
                    <span class="text-white font-semibold text-sm">Apex Brains</span>
                    <span class="ml-2 text-xs text-blue-100 bg-fran-dark rounded px-1.5 py-0.5">Competition</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('external.notifications.index') }}" class="text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </a>
                <a href="{{ route('external.profile') }}" class="w-7 h-7 rounded-full bg-fran-dark flex items-center justify-center text-white text-xs font-bold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </a>
            </div>
        </div>
    </header>

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

    <main class="flex-1 overflow-y-auto pb-20">
        @yield('content')
    </main>

    {{-- Bottom Navigation (simpler than internal) --}}
    <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-sm md:max-w-full bg-white border-t border-border z-30 safe-bottom">
        <div class="flex justify-around items-center h-14">
            <x-bottom-nav-item route="external.home" icon="home" label="Home" color="fran" />
            <x-bottom-nav-item route="external.practice.index" icon="zap" label="Practice" color="fran" />
            <x-bottom-nav-item route="external.competitions.index" icon="trophy" label="Exams" color="fran" />
            <x-bottom-nav-item route="external.results" icon="bar-chart-2" label="Results" color="fran" />
            <x-bottom-nav-item route="external.profile" icon="user" label="Profile" color="fran" />
        </div>
    </nav>
</div>
</body>
</html>
