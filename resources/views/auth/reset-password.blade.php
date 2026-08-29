<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password — Apex Brains Academy</title>
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

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.08)] px-6 py-6 mt-5 text-left">

            <h2 class="text-center text-[16px] font-bold text-gray-900 mb-1">Reset Password</h2>
            <p class="text-center text-[12px] text-gray-400 mb-5">Choose a new password for your account</p>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-[11px] font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus
                           placeholder="you@example.com"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-[12px] bg-white focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent placeholder:text-gray-300 transition">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-gray-700 mb-1.5">New Password</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-[12px] bg-white focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-gray-700 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-[12px] bg-white focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent transition">
                </div>

                <button type="submit"
                        class="w-full bg-fran text-white rounded-full py-3 text-[12px] font-bold hover:bg-fran-dark transition-colors shadow-sm">
                    Reset Password
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-[10px] text-gray-400 mt-4">SSL Encrypted | Apex Brains Academy</p>

    </div>

</body>
</html>
