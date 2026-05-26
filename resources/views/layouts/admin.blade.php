<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Apex Brains Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-bg-light font-sans">
<div class="flex h-full">

    {{-- Sidebar --}}
    <aside class="flex-shrink-0 bg-admin flex flex-col" style="width:220px">
        {{-- Logo --}}
        <div class="px-5 py-4 border-b border-admin-mid flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-logo-red flex items-center justify-center text-white font-bold text-sm flex-shrink-0">AB</div>
            <div class="min-w-0">
                <p class="text-white font-bold text-sm leading-tight">Apex Brains</p>
                <p class="text-gray-400 text-xs">Management System</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <x-admin-nav-item route="admin.dashboard"        label="Dashboard"     icon="grid" />
            <x-admin-nav-item route="admin.franchises.index" label="Franchises"    icon="building" />
            <x-admin-nav-item route="admin.questions.index"  label="Question Bank" icon="help-circle" />
            <x-admin-nav-item route="admin.levels.index"     label="Curriculum"    icon="layers" />
            <x-admin-nav-item route="admin.settings"         label="Settings"      icon="settings" />
            <x-admin-nav-item route="admin.revenue"          label="Finance"       icon="bar-chart" />
        </nav>

        {{-- User area --}}
        <div class="px-4 py-3 border-t border-admin-mid">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">{{ auth()->user()->name }}</p>
                    <p class="text-gray-400 text-xs">Super Admin</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out"
                            class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="h-14 bg-admin border-b border-admin-mid flex items-center px-6 gap-4 flex-shrink-0">
            <div class="flex items-center gap-1 text-sm text-gray-400">
                <span class="font-semibold text-white">Apex Brains Admin Portal</span>
            </div>
            <div class="ml-auto flex items-center gap-4">
                @if(request()->routeIs('admin.audit-log'))
                    <span class="text-fran text-sm font-medium">Audit Log</span>
                @else
                    <a href="{{ route('admin.audit-log') }}"
                       class="text-gray-400 hover:text-white text-sm transition-colors">Audit Log</a>
                @endif
                <a href="{{ route('admin.help') }}"
                   class="text-gray-400 hover:text-white text-sm transition-colors {{ request()->routeIs('admin.help') ? 'text-white' : '' }}">Help</a>
                <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
            </div>
        </header>

        {{-- Breadcrumb + Page header --}}
        <div class="bg-white border-b border-border px-6 py-3 flex items-center justify-between flex-shrink-0">
            <div>
                <x-breadcrumb />
                @hasSection('page-title')
                    <h1 class="text-lg font-bold text-admin mt-0.5">@yield('page-title')</h1>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @yield('page-actions')
            </div>
        </div>

        {{-- Content --}}
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
@stack('scripts')
</body>
</html>
