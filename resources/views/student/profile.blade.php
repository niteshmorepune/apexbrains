@extends('layouts.student')
@section('title', 'Profile')

@section('content')
<x-student-header title="Profile" :back="route('student.home')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Profile card --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-stu text-white flex items-center justify-center font-black text-2xl flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="font-black text-lg text-gray-900 truncate">{{ $user->name }}</p>
                <p class="text-gray-400 text-xs truncate">{{ $user->email }}</p>
                @if($student?->currentLevel)
                    <span class="mt-1 inline-block bg-stu-light text-stu text-xs px-2 py-0.5 rounded-full font-bold">{{ $student->currentLevel->title }} Student</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 mt-4">
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="font-black text-lg text-gray-800">{{ $examCount }}</p>
                <p class="text-gray-400 text-[11px]">Exams taken</p>
            </div>
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="font-black text-lg text-stu">{{ $passedCount }}</p>
                <p class="text-gray-400 text-[11px]">Passed</p>
            </div>
            <div class="bg-bg-light rounded-xl p-3 text-center">
                <p class="font-black text-lg text-fran">{{ $practiceCount }}</p>
                <p class="text-gray-400 text-[11px]">Practice</p>
            </div>
        </div>
    </div>

    {{-- Student info --}}
    @if($student)
        <div class="bg-white rounded-2xl border border-border p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Student Info</p>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-400">Student Code</span><span class="font-mono font-semibold text-gray-700">{{ $student->student_code }}</span></div>
                @if($student->date_of_birth)<div class="flex justify-between"><span class="text-gray-400">Date of Birth</span><span class="font-medium text-gray-700">{{ $student->date_of_birth->format('d M Y') }}</span></div>@endif
                @if($student->gender)<div class="flex justify-between"><span class="text-gray-400">Gender</span><span class="font-medium text-gray-700 capitalize">{{ $student->gender }}</span></div>@endif
                @if($student->enrollment_date)<div class="flex justify-between"><span class="text-gray-400">Enrolled</span><span class="font-medium text-gray-700">{{ $student->enrollment_date->format('d M Y') }}</span></div>@endif
                @if($student->franchise)<div class="flex justify-between"><span class="text-gray-400">Academy</span><span class="font-medium text-gray-700">{{ $student->franchise->name }}</span></div>@endif
            </div>
        </div>
    @endif

    {{-- Edit profile --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Edit Profile</p>

        @if(session('success'))
            <div class="mb-3 p-3 bg-stu-light border border-stu/30 rounded-xl text-sm text-stu-dark">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Display Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="100"
                       class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="border-t border-border pt-4">
                <p class="text-xs text-gray-400 mb-3">Change password (leave blank to keep current)</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Current Password</label>
                        <input type="password" name="current_password" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                        @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">New Password</label>
                        <input type="password" name="password" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full py-3 bg-fran text-white rounded-xl text-sm font-bold">Save Changes</button>
        </form>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full py-3 border border-border text-gray-500 rounded-xl text-sm font-bold">Sign Out</button>
    </form>

</div>
@endsection
