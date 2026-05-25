<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Apex Brains Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-bg-light font-sans" x-data="{ sidebarOpen: true }">
<div class="flex h-full">

    {{-- Sidebar --}}
    <aside class="w-55 flex-shrink-0 bg-admin flex flex-col" style="width:220px">
        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-admin-mid">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-logo-red flex items-center justify-center text-white font-bold text-sm">AB</div>
                <div>
                    <p class="text-white font-semibold text-sm leading-tight">Apex Brains</p>
                    <p class="text-admin-light text-xs">Admin Panel</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <x-admin-nav-item route="admin.dashboard" icon="grid" label="Dashboard" />
            <x-admin-nav-item route="admin.franchises.index" icon="building" label="Franchises" />
            <x-admin-nav-item route="admin.levels.index" icon="layers" label="Levels" />
            <x-admin-nav-item route="admin.questions.index" icon="help-circle" label="Question Bank" />
            <x-admin-nav-item route="admin.questions.audio" icon="mic" label="Audio Generator" />
            <x-admin-nav-item route="admin.pdf-uploads.index" icon="upload" label="PDF Upload" />
            <x-admin-nav-item route="admin.competitions.index" icon="trophy" label="Competitions" />
            <x-admin-nav-item route="admin.revenue" icon="bar-chart" label="Revenue" />
            <x-admin-nav-item route="admin.leaderboard" icon="award" label="Leaderboard" />
            <x-admin-nav-item route="admin.commissions.index" icon="dollar-sign" label="Commissions" />
            <x-admin-nav-item route="admin.audit-log" icon="shield" label="Audit Log" />
            <x-admin-nav-item route="admin.settings" icon="settings" label="Settings" />
        </nav>

        {{-- User info --}}
        <div class="px-4 py-4 border-t border-admin-mid">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-admin-light flex items-center justify-center text-white text-xs font-bold">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-admin-light text-xs">Super Admin</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-admin-light hover:text-white text-xs">Out</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        {{-- Top bar --}}
        <header class="h-14 bg-white border-b border-border flex items-center px-6 gap-4 flex-shrink-0">
            <x-breadcrumb />
            <div class="ml-auto flex items-center gap-3">
                <span class="text-sm text-gray-500">{{ now()->format('d M Y') }}</span>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" class="mb-4" />
            @endif
            @if(session('error'))
                <x-alert type="error" :message="session('error')" class="mb-4" />
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
