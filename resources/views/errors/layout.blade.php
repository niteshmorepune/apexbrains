<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Apex Brains Academy</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-bg-light flex items-center justify-center p-4 font-sans">
<div class="w-full max-w-sm text-center">
    <div class="w-16 h-16 rounded-2xl bg-logo-red mx-auto flex items-center justify-center mb-6">
        <span class="text-white text-2xl font-bold">AB</span>
    </div>

    <p class="text-7xl font-black text-gray-200 mb-2">@yield('code')</p>
    <h1 class="text-xl font-bold text-gray-800 mb-2">@yield('heading')</h1>
    <p class="text-sm text-gray-500 mb-8">@yield('message')</p>

    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"
       class="inline-block px-5 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark mr-2">
        Go Back
    </a>
    <a href="/"
       class="inline-block px-5 py-2.5 border border-border text-gray-600 rounded-xl text-sm hover:bg-white">
        Home
    </a>
</div>
</body>
</html>
