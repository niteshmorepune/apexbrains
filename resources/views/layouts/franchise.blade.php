<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ auth()->user()->franchise->name ?? 'Franchise' }}</title>
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
           class="fixed inset-y-0 left-0 z-40 w-[220px] flex-shrink-0 bg-fran-dark flex flex-col
                  transform -translate-x-full transition-transform duration-200 ease-in-out
                  lg:static lg:translate-x-0 lg:z-auto">
        {{-- Logo --}}
        <div class="px-4 py-4 border-b border-fran">
            <div class="bg-white rounded-xl px-3 py-2.5 flex items-center justify-center">
                <x-brand-logo size="sm" />
            </div>
            <p class="text-blue-200 text-[11px] mt-2.5 truncate text-center">{{ auth()->user()->franchise->name ?? 'Franchise' }} · Branch Panel</p>
        </div>

        {{-- Navigation --}}
        <nav @click="sidebarOpen = false" class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <x-franchise-nav-item route="franchise.dashboard" icon="grid" label="Dashboard" />
            <x-franchise-nav-item route="franchise.students.index" icon="users" label="Students" />
            <x-franchise-nav-item route="franchise.fees.index" icon="credit-card" label="Fees" />
            <x-franchise-nav-item route="franchise.exams.index" icon="file-text" label="Exams" />
            <x-franchise-nav-item route="franchise.class-practice.index" icon="monitor" label="Class Practice" />
            <x-franchise-nav-item route="franchise.competitions.index" icon="trophy" label="Competitions" />
            <x-franchise-nav-item route="franchise.certificates.index" icon="award" label="Certificates" />
            <x-franchise-nav-item route="franchise.promotions.index" icon="trending-up" label="Promotions" />
            <x-franchise-nav-item route="franchise.reports.index" icon="bar-chart-2" label="Reports" />
            <x-franchise-nav-item route="franchise.parents.index" icon="phone" label="Parent Directory" />
            <x-franchise-nav-item route="franchise.notifications.index" icon="bell" label="Notifications" />
            <x-franchise-nav-item route="franchise.help" icon="help-circle" label="Help Guide" />
        </nav>

        {{-- User info --}}
        <div class="px-4 py-4 border-t border-fran">
            <div class="flex items-center gap-3">
                <a href="{{ route('franchise.profile') }}" class="flex items-center gap-3 flex-1 min-w-0 group">
                    <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate group-hover:underline">{{ auth()->user()->name }}</p>
                        <p class="text-blue-200 text-xs">Franchise Admin</p>
                    </div>
                </a>
                <form method="POST" action="{{ route('franchise.logout') }}">
                    @csrf
                    <button type="submit" class="text-blue-200 hover:text-white text-xs" title="Log out">Out</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-14 bg-fran border-b border-fran flex items-center px-4 lg:px-6 gap-3 lg:gap-4 flex-shrink-0">
            {{-- Mobile hamburger --}}
            <button type="button" @click="sidebarOpen = true"
                    class="lg:hidden text-blue-100 hover:text-white flex-shrink-0" aria-label="Open menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex items-center gap-1 text-sm min-w-0">
                <span class="font-semibold text-white truncate">{{ auth()->user()->franchise->name ?? 'Branch' }}</span>
                <span class="text-blue-200 ml-2 hidden sm:inline">{{ now()->format('d M Y') }}</span>
            </div>
            <div class="ml-auto flex items-center gap-2 lg:gap-3 flex-wrap flex-shrink-0">
                @yield('page-actions')
            </div>
        </header>

        {{-- Page title bar --}}
        @hasSection('page-title')
        <div class="bg-white border-b border-border px-4 lg:px-6 py-3 flex items-center justify-between flex-shrink-0">
            <h1 class="text-base lg:text-lg font-bold text-fran truncate">@yield('page-title')</h1>
        </div>
        @endif

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
