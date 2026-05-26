@extends('layouts.external')
@section('title', 'Profile')

@section('content')
<div class="p-4 space-y-4">

    {{-- Profile card --}}
    <div class="bg-fran rounded-2xl p-5 text-white">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center text-white font-black text-xl flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-lg">{{ $user->name }}</p>
                <p class="text-white/70 text-xs">{{ $user->email }}</p>
                <span class="mt-1 inline-block bg-white/20 text-white text-xs px-2 py-0.5 rounded-full">
                    Competition Participant
                </span>
            </div>
        </div>
    </div>

    {{-- Student info (read-only) --}}
    @if($student)
        <div class="bg-white rounded-2xl border border-border p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Account Info</p>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Student Code</span>
                    <span class="font-mono font-medium">{{ $student->student_code }}</span>
                </div>
                @if($student->date_of_birth)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date of Birth</span>
                        <span class="font-medium">{{ $student->date_of_birth->format('d M Y') }}</span>
                    </div>
                @endif
                @if($student->city)
                    <div class="flex justify-between">
                        <span class="text-gray-500">City</span>
                        <span class="font-medium">{{ $student->city }}</span>
                    </div>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-3">Contact your franchise to update personal details.</p>
        </div>
    @endif

    {{-- Change password --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Change Password</p>

        @if(session('success'))
            <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('external.profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">New Password</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                Update Password
            </button>
        </form>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                class="w-full py-3 border border-border text-gray-500 rounded-xl text-sm font-semibold hover:bg-bg-light">
            Sign Out
        </button>
    </form>

</div>
@endsection
