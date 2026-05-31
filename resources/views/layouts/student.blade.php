<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') — Apex Brains</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { -webkit-tap-highlight-color: transparent; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0.5rem); }
    </style>
</head>
<body class="bg-bg-light md:bg-slate-200 min-h-screen flex flex-col">

{{-- App column: phone-width on mobile, centered framed column on desktop --}}
<div class="max-w-sm md:max-w-md mx-auto w-full flex flex-col min-h-screen relative bg-bg-light md:shadow-xl">

    {{-- Green header — INTERNAL STUDENT (NEVER dark navy) --}}
    <header class="bg-stu sticky top-0 z-30 flex-shrink-0">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-white flex items-center justify-center">
                    <span class="text-stu font-bold text-xs">AB</span>
                </div>
                <span class="text-white font-semibold text-sm">Apex Brains</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('student.notifications.index') }}" class="text-white relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </a>
                <a href="{{ route('student.profile') }}" class="w-7 h-7 rounded-full bg-stu-dark flex items-center justify-center text-white text-xs font-bold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </a>
            </div>
        </div>
        @hasSection('subheader')
            <div class="px-4 pb-3">@yield('subheader')</div>
        @endif
    </header>

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

    {{-- Page content --}}
    <main class="flex-1 overflow-y-auto pb-20">
        @yield('content')
    </main>

    {{-- Bottom Navigation --}}
    <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-sm md:max-w-md bg-white border-t border-border z-30 safe-bottom">
        <div class="flex justify-around items-center h-14">
            <x-bottom-nav-item route="student.home" icon="home" label="Home" />
            <x-bottom-nav-item route="student.practice.index" icon="zap" label="Practice" />
            <x-bottom-nav-item route="student.exams.index" icon="file-text" label="Exams" />
            <x-bottom-nav-item route="student.results" icon="bar-chart-2" label="Results" />
            <x-bottom-nav-item route="student.profile" icon="user" label="Profile" />
        </div>
    </nav>
</div>
</body>
</html>
