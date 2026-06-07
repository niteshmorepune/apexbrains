@extends('layouts.external')
@section('title', 'Profile')

@section('content')
<x-student-header title="Profile" :back="route('external.home')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Profile card --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-fran text-white flex items-center justify-center font-black text-2xl flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="font-black text-lg text-gray-900 truncate">{{ $user->name }}</p>
                <p class="text-gray-400 text-xs truncate">{{ $user->email }}</p>
                <span class="mt-1 inline-block bg-fran-light text-fran text-xs px-2 py-0.5 rounded-full font-bold">Competition Participant</span>
            </div>
        </div>
    </div>

    {{-- Account info --}}
    @if($student)
        <div class="bg-white rounded-2xl border border-border p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Account Info</p>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-400">Student Code</span><span class="font-mono font-semibold text-gray-700">{{ $student->student_code }}</span></div>
                @if($student->date_of_birth)<div class="flex justify-between"><span class="text-gray-400">Date of Birth</span><span class="font-medium text-gray-700">{{ $student->date_of_birth->format('d M Y') }}</span></div>@endif
                @if($student->city)<div class="flex justify-between"><span class="text-gray-400">City</span><span class="font-medium text-gray-700">{{ $student->city }}</span></div>@endif
            </div>
            <p class="text-xs text-gray-400 mt-3">Contact your branch to update personal details.</p>
        </div>
    @endif

    {{-- Change password --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Change Password</p>
        @if(session('success'))
            <div class="mb-3 p-3 bg-stu-light border border-stu/30 rounded-xl text-sm text-stu-dark">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('external.profile.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Current Password</label>
                <input type="password" name="current_password" required class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">New Password</label>
                <input type="password" name="password" required minlength="8" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirm New Password</label>
                <input type="password" name="password_confirmation" required class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
            </div>
            <button type="submit" class="w-full py-3 bg-fran text-white rounded-xl text-sm font-bold">Update Password</button>
        </form>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full py-3 border border-border text-gray-500 rounded-xl text-sm font-bold">Sign Out</button>
    </form>

</div>
@endsection
