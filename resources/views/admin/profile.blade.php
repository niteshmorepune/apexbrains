@extends('layouts.admin')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')

<div class="max-w-2xl space-y-4">

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3">
            <p class="text-sm text-red-600 font-medium">{{ $errors->first() }}</p>
        </div>
    @endif

    {{-- Identity card --}}
    <div class="bg-white rounded-2xl border border-border p-6 flex items-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-fran flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div>
            <p class="text-lg font-bold text-admin">{{ $user->name }}</p>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
            <span class="inline-block mt-1 text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full font-medium">Super Admin</span>
        </div>
    </div>

    {{-- Account details --}}
    <form method="POST" action="{{ route('admin.profile.update') }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-border p-6 mb-4">
            <h2 class="text-sm font-bold text-admin mb-4">Account Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>
        </div>

        {{-- Change password --}}
        <div class="bg-white rounded-2xl border border-border p-6 mb-4">
            <h2 class="text-sm font-bold text-admin mb-1">Change Password</h2>
            <p class="text-xs text-gray-400 mb-4">Leave blank to keep your current password.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                    <input type="password" name="password" autocomplete="new-password" placeholder="Min 8 characters"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
            </div>
        </div>

        <button type="submit"
                class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            Save Changes
        </button>
    </form>

    {{-- Sign out (separate form) --}}
    <form method="POST" action="{{ route('admin.logout') }}" class="pt-2">
        @csrf
        <button type="submit"
                class="px-5 py-2.5 border border-red-200 text-red-500 rounded-xl text-sm font-semibold hover:bg-red-50 transition-colors">
            Sign Out
        </button>
    </form>

</div>

@endsection
