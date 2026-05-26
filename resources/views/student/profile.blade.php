@extends('layouts.student')
@section('title', 'Profile')

@section('content')
<div class="p-4 space-y-4">

    {{-- Profile card --}}
    <div class="bg-stu rounded-2xl p-5 text-white">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center text-white font-black text-xl flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-lg">{{ $user->name }}</p>
                <p class="text-white/70 text-xs">{{ $user->email }}</p>
                @if($student?->currentLevel)
                    <span class="mt-1 inline-block bg-white/20 text-white text-xs px-2 py-0.5 rounded-full">
                        Level {{ $student->currentLevel->number }}
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 mt-4">
            <div class="bg-white/10 rounded-xl p-2 text-center">
                <p class="font-black text-lg">{{ $examCount }}</p>
                <p class="text-white/60 text-xs">Exams taken</p>
            </div>
            <div class="bg-white/10 rounded-xl p-2 text-center">
                <p class="font-black text-lg">{{ $passedCount }}</p>
                <p class="text-white/60 text-xs">Passed</p>
            </div>
            <div class="bg-white/10 rounded-xl p-2 text-center">
                <p class="font-black text-lg">{{ $practiceCount }}</p>
                <p class="text-white/60 text-xs">Practice</p>
            </div>
        </div>
    </div>

    {{-- Student info --}}
    @if($student)
        <div class="bg-white rounded-2xl border border-border p-5">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Student Info</p>
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
                @if($student->gender)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Gender</span>
                        <span class="font-medium capitalize">{{ $student->gender }}</span>
                    </div>
                @endif
                @if($student->enrollment_date)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Enrolled</span>
                        <span class="font-medium">{{ $student->enrollment_date->format('d M Y') }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Edit profile --}}
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Edit Profile</p>

        @if(session('success'))
            <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                {{ session('success') }}
            </div>
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
                        <input type="password" name="current_password"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                        @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">New Password</label>
                        <input type="password" name="password"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-stu">
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-stu text-white rounded-xl text-sm font-semibold hover:bg-stu-dark">
                Save Changes
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
