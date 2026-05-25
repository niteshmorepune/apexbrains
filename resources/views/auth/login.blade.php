<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Apex Brains Academy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-bg-light flex items-center justify-center p-4 font-sans">
<div class="w-full max-w-sm">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-logo-red mx-auto flex items-center justify-center mb-4">
            <span class="text-white text-2xl font-bold">AB</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Apex Brains Academy</h1>
        <p class="text-sm text-gray-500 mt-1">Sign in to your account</p>
    </div>

    {{-- Error --}}
    @if($errors->any())
        <x-alert type="error" :message="$errors->first()" class="mb-4" />
    @endif

    <form method="POST" action="{{ route('login') }}" class="bg-white rounded-2xl shadow-sm border border-border p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <input type="password" name="password" required
                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
        </div>
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded">
                Remember me
            </label>
        </div>
        <button type="submit"
                class="w-full bg-fran text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-fran-dark transition-colors">
            Sign In
        </button>
    </form>

    <p class="text-center text-xs text-gray-400 mt-6">
        Certificate verification?
        <a href="{{ route('certificate.verify', 'lookup') }}" class="text-fran hover:underline">Verify here</a>
    </p>
</div>
</body>
</html>
