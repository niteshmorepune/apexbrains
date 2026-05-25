<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login — Apex Brains</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-admin flex items-center justify-center p-4 font-sans">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-logo-red mx-auto flex items-center justify-center mb-4">
            <span class="text-white text-2xl font-bold">AB</span>
        </div>
        <h1 class="text-xl font-bold text-white">Apex Brains Admin</h1>
        <p class="text-sm text-gray-400 mt-1">Super admin access only</p>
    </div>

    @if($errors->any())
        <x-alert type="error" :message="$errors->first()" class="mb-4" />
    @endif

    <form method="POST" action="{{ route('admin.login.post') }}"
          class="bg-admin-mid rounded-2xl border border-admin-light p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full bg-admin border border-admin-light rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
            <input type="password" name="password" required
                   class="w-full bg-admin border border-admin-light rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
        </div>
        <button type="submit"
                class="w-full bg-fran text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-fran-dark transition-colors">
            Sign In
        </button>
    </form>

    <p class="text-center text-xs text-gray-600 mt-6">Apex Brains Academy Pvt. Ltd., Pune</p>
</div>
</body>
</html>
