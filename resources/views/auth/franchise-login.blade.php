<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Branch Sign In — Apex Brains Academy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-[#F5F8FE]" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">

<div class="min-h-screen grid lg:grid-cols-[3fr_5fr]">

    {{-- Left branding panel --}}
    <div class="relative flex flex-col justify-center px-12 py-16 bg-[#F5F8FE] overflow-hidden">

        {{-- Decorative blobs --}}
        <div class="absolute top-[-80px] right-[-60px] w-72 h-72 rounded-full bg-fran/[0.07] pointer-events-none"></div>
        <div class="absolute bottom-[-60px] left-[-40px] w-56 h-56 rounded-full bg-fran/[0.05] pointer-events-none"></div>

        {{-- Logo --}}
        <div class="flex items-center gap-3 mb-8">
            <span class="text-logo-red font-black text-[26px] italic leading-none select-none">A</span>
            <div class="leading-tight">
                <div class="text-[20px] font-extrabold tracking-tight">
                    <span class="text-logo-red">A</span><span class="text-fran">p</span><span class="text-[#34A853]">e</span><span class="text-logo-amber">x</span>
                    <span class="text-logo-red"> B</span><span class="text-fran">r</span><span class="text-[#34A853]">a</span><span class="text-logo-amber">i</span><span class="text-logo-red">n</span><span class="text-fran">s</span>
                </div>
                <div class="text-[12px] font-semibold tracking-[0.18em] text-fran uppercase">Abacus</div>
            </div>
        </div>

        {{-- Taglines --}}
        <h2 class="text-[16px] font-normal text-gray-700 mb-1">International Abacus Programme</h2>
        <p class="text-[13px] text-gray-500 mb-8">"Explore your Potential"</p>

        {{-- Portal description --}}
        <p class="text-[13px] text-gray-600 font-medium mb-2">Franchise Owner and Branch Manager Portal</p>
        <p class="text-[11px] text-gray-400">Manage students, attendance, fees and progress</p>
    </div>

    {{-- Right form panel --}}
    <div class="flex items-center justify-center px-8 py-16 bg-white">
        <div class="w-full max-w-sm">

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.08)] border border-gray-100 px-8 py-8">

                <h1 class="text-[22px] font-bold text-gray-900 mb-1">Branch Sign In</h1>
                <p class="text-[13px] text-gray-400 mb-6">Franchise Owner or Manager Access</p>

                {{-- Error --}}
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                        <p class="text-[12px] text-red-600 font-medium">{{ $errors->first() }}</p>
                    </div>
                @endif

                <div x-data="{ showForgot: false }">
                <form method="POST" action="{{ route('franchise.login.post') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-medium text-gray-600 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="branch@apexbrains.in"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-[12px] bg-white focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent placeholder:text-gray-300 transition">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-[11px] font-medium text-gray-600">Password</label>
                            <button type="button" @click="showForgot = !showForgot" class="text-[12px] text-fran hover:underline">Forgot password?</button>
                        </div>
                        <input type="password" name="password" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-[12px] bg-white focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent transition">
                    </div>

                    <p x-show="showForgot" x-transition class="text-[11px] text-gray-500 bg-blue-50 rounded-lg px-3 py-2 -mt-2">
                        To reset your password, contact the system administrator.
                    </p>

                    <button type="submit"
                            class="w-full bg-fran text-white rounded-full py-3 text-[12px] font-bold hover:bg-fran-dark transition-colors shadow-sm mt-2">
                        Sign In to Branch Portal
                    </button>
                </form>
                </div>

                <p class="text-center text-[11px] text-gray-400 mt-5">SSL Encrypted | ISO 9001:2015</p>
            </div>

        </div>
    </div>

</div>

</body>
</html>
