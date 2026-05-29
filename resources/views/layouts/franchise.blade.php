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
<div class="flex h-full">

    {{-- Sidebar --}}
    <aside class="flex-shrink-0 bg-fran-dark flex flex-col" style="width:220px">
        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-fran">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-fran font-bold text-sm">AB</div>
                <div>
                    <p class="text-white font-semibold text-sm leading-tight truncate">{{ auth()->user()->franchise->name ?? 'Franchise' }}</p>
                    <p class="text-blue-200 text-xs">Branch Panel</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
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
                <div class="w-8 h-8 rounded-full bg-fran flex items-center justify-center text-white text-xs font-bold">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-blue-200 text-xs">Franchise Admin</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-blue-200 hover:text-white text-xs">Out</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-14 bg-fran border-b border-fran flex items-center px-6 gap-4 flex-shrink-0">
            <div class="flex items-center gap-1 text-sm">
                <span class="font-semibold text-white">{{ auth()->user()->franchise->name ?? 'Branch' }}</span>
                <span class="text-blue-200 ml-2">{{ now()->format('d M Y') }}</span>
            </div>
            <div class="ml-auto flex items-center gap-3">
                @yield('page-actions')
            </div>
        </header>

        {{-- Page title bar --}}
        @hasSection('page-title')
        <div class="bg-white border-b border-border px-6 py-3 flex items-center justify-between flex-shrink-0">
            <h1 class="text-lg font-bold text-fran">@yield('page-title')</h1>
        </div>
        @endif

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
