<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password — Apex Brains Academy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#F5F8FE] flex items-center justify-center p-4 font-sans relative overflow-hidden">

    {{-- Decorative background blobs --}}
    <div class="absolute top-[-80px] left-[-80px] w-56 h-56 rounded-full bg-fran/[0.07] pointer-events-none"></div>
    <div class="absolute bottom-[-80px] right-[-80px] w-72 h-72 rounded-full bg-fran/[0.07] pointer-events-none"></div>

    <div class="w-full max-w-[340px] text-center">

        {{-- Logo --}}
        @if(!empty($appSettings['logo_path']))
            <img src="{{ Storage::url($appSettings['logo_path']) }}" alt="{{ $appSettings['app_name'] ?? 'Apex Brains' }}" class="h-14 w-auto mx-auto mb-3">
        @else
            <div class="text-5xl leading-none mb-3">🧮</div>
        @endif

        {{-- Error alert --}}
        @if($errors->any())
            <div class="mt-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-left">
                <p class="text-[12px] text-red-600 font-medium">{{ $errors->first() }}</p>
            </div>
        @endif

        @if(session('status'))
            <div class="mt-4 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-left">
                <p class="text-[12px] text-green-600 font-medium">{{ session('status') }}</p>
            </div>
        @endif

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.08)] px-6 py-6 mt-5 text-left">

            <h2 class="text-center text-[16px] font-bold text-gray-900 mb-1">Forgot Password</h2>
            <p class="text-center text-[12px] text-gray-400 mb-5">Enter your email and we'll send you a reset link</p>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="you@example.com"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-[12px] bg-white focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent placeholder:text-gray-300 transition">
                </div>

                <button type="submit"
                        class="w-full bg-fran text-white rounded-full py-3 text-[12px] font-bold hover:bg-fran-dark transition-colors shadow-sm">
                    Send Reset Link
                </button>
            </form>

            <p class="text-center text-[11px] text-gray-400 mt-4">
                <a href="{{ route('login') }}" class="text-fran hover:underline">Back to Sign In</a>
            </p>
        </div>

        {{-- Footer --}}
        <p class="text-[10px] text-gray-400 mt-4">SSL Encrypted | Apex Brains Academy</p>

    </div>

</body>
</html>
