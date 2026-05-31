<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Apex Brains Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-bg-light" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">
<div class="flex h-full" x-data="{ sidebarOpen: false }">

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-cloak x-transition.opacity
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="{ '!translate-x-0': sidebarOpen }"
           class="fixed inset-y-0 left-0 z-40 w-[220px] flex-shrink-0 bg-admin flex flex-col
                  transform -translate-x-full transition-transform duration-200 ease-in-out
                  lg:static lg:translate-x-0 lg:z-auto">
        {{-- Logo --}}
        <div class="px-5 py-4 border-b border-admin-mid flex items-center gap-3">
            @if(!empty($appSettings['logo_path']))
                <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center overflow-hidden flex-shrink-0">
                    <img src="{{ Storage::url($appSettings['logo_path']) }}" alt="Logo" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="w-9 h-9 rounded-xl bg-logo-red flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($appSettings['app_name'] ?? 'AB', 0, 2)) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-white font-bold text-sm leading-tight truncate">{{ $appSettings['app_name'] ?? 'Apex Brains' }}</p>
                <p class="text-gray-400 text-xs">Management System</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav @click="sidebarOpen = false" class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <x-admin-nav-item route="admin.dashboard"          label="Dashboard"     icon="grid" />
            <x-admin-nav-item route="admin.franchises.index" label="Franchises"    icon="building" />
            <x-admin-nav-item route="admin.questions.index"  label="Question Bank" icon="help-circle" />
            <x-admin-nav-item route="admin.levels.index"       label="Curriculum"      icon="layers" />
            <x-admin-nav-item route="admin.resources.index"   label="Resources"       icon="file-text" />
            <x-admin-nav-item route="admin.leaderboard"       label="Leaderboard"     icon="trophy" />
            <x-admin-nav-item route="admin.competitions.index" label="Competitions"   icon="award" />
            <x-admin-nav-item route="admin.competition-papers.index" label="Prac. Papers" icon="layers" />
            <x-admin-nav-item route="admin.settings"         label="Settings"      icon="settings" />
            <x-admin-nav-item route="admin.revenue"          label="Finance"       icon="bar-chart" />
        </nav>

        {{-- User area --}}
        <div class="px-4 py-3 border-t border-admin-mid">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.profile') }}" class="flex items-center gap-2 flex-1 min-w-0 group">
                    <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-xs font-semibold truncate group-hover:underline">{{ auth()->user()->name }}</p>
                        <p class="text-gray-400 text-xs">Super Admin</p>
                    </div>
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">
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
        <header class="h-14 bg-admin border-b border-admin-mid flex items-center px-4 lg:px-6 gap-3 lg:gap-4 flex-shrink-0">
            {{-- Mobile hamburger --}}
            <button type="button" @click="sidebarOpen = true"
                    class="lg:hidden text-gray-300 hover:text-white flex-shrink-0" aria-label="Open menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex items-center gap-1 text-sm text-gray-400 min-w-0">
                <span class="font-semibold text-white truncate">{{ $appSettings['app_name'] ?? 'Apex Brains' }} <span class="hidden sm:inline">Admin Portal</span></span>
            </div>
            <div class="ml-auto flex items-center gap-3 lg:gap-4 flex-shrink-0">
                @if(request()->routeIs('admin.audit-log'))
                    <span class="text-fran text-sm font-medium">Audit Log</span>
                @else
                    <a href="{{ route('admin.audit-log') }}"
                       class="text-gray-400 hover:text-white text-sm transition-colors">Audit Log</a>
                @endif
                <a href="{{ route('admin.help') }}"
                   class="text-gray-400 hover:text-white text-sm transition-colors {{ request()->routeIs('admin.help') ? 'text-white' : '' }}">Help</a>
                <a href="{{ route('admin.profile') }}" title="My Profile"
                   class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold hover:ring-2 hover:ring-white/30 transition">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </a>
            </div>
        </header>

        {{-- Breadcrumb + Page header --}}
        <div class="bg-white border-b border-border px-4 lg:px-6 py-3 flex items-center justify-between gap-3 flex-wrap flex-shrink-0">
            <div class="min-w-0">
                <x-breadcrumb />
                @hasSection('page-title')
                    <h1 class="text-base lg:text-lg font-bold text-admin mt-0.5 truncate">@yield('page-title')</h1>
                @endif
            </div>
            <div class="flex items-center gap-2 lg:gap-3 flex-wrap">
                @yield('page-actions')
            </div>
        </div>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
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
