<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — Apex Brains Academy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#F5F8FE] flex items-center justify-center p-4 font-sans relative overflow-hidden">

    {{-- Decorative background blobs --}}
    <div class="absolute top-[-80px] left-[-80px] w-56 h-56 rounded-full bg-fran/[0.07] pointer-events-none"></div>
    <div class="absolute bottom-[-80px] right-[-80px] w-72 h-72 rounded-full bg-fran/[0.07] pointer-events-none"></div>
    <div class="absolute top-1/3 right-[-40px] w-32 h-32 rounded-full bg-fran/[0.05] pointer-events-none"></div>

    <div class="w-full max-w-[340px] text-center">

        {{-- Abacus icon --}}
        <div class="text-5xl leading-none mb-3">🧮</div>

        {{-- Apex Brains logo --}}
        <div class="flex items-center justify-center gap-2 mb-1">
            <span class="text-logo-red font-black text-[22px] italic leading-none select-none">A</span>
            <div class="text-left leading-tight">
                <div class="text-[17px] font-extrabold tracking-tight">
                    <span class="text-logo-red">A</span><span class="text-fran">p</span><span class="text-[#34A853]">e</span><span class="text-logo-amber">x</span>
                    <span class="text-logo-red"> B</span><span class="text-fran">r</span><span class="text-[#34A853]">a</span><span class="text-logo-amber">i</span><span class="text-logo-red">n</span><span class="text-fran">s</span>
                </div>
                <div class="text-[11px] font-semibold tracking-[0.18em] text-fran">Abacus</div>
            </div>
        </div>

        {{-- Taglines --}}
        <p class="text-[12px] text-gray-500 mt-2 leading-snug">International Abacus Programme</p>
        <p class="text-[12px] text-gray-500 leading-snug">"Explore your Potential"</p>

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

        {{-- Login card --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.08)] px-6 py-6 mt-5 text-left">

            <h2 class="text-center text-[16px] font-bold text-gray-900 mb-1">Sign In</h2>
            <p class="text-center text-[12px] text-gray-400 mb-5">Continue your abacus journey</p>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-medium text-gray-700 mb-1.5">Email / Student ID</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="Your email or student ID..."
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-[12px] bg-white focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent placeholder:text-gray-300 transition">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-[12px] bg-white focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent transition">
                </div>

                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="text-[11px] text-fran hover:underline -mt-1">Forgot password?</button>
                    <p x-show="open" x-transition class="text-[11px] text-gray-500 mt-1 bg-blue-50 rounded-lg px-3 py-2">
                        Please contact your branch administrator to reset your password.
                    </p>
                </div>

                <button type="submit"
                        class="w-full bg-fran text-white rounded-full py-3 text-[12px] font-bold hover:bg-fran-dark transition-colors shadow-sm">
                    Sign In
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-[10px] text-gray-400 mt-4">SSL Encrypted | Apex Brains Academy</p>

    </div>

</body>
</html>
