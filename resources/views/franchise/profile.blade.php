@extends('layouts.franchise')
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
            <p class="text-lg font-bold text-fran">{{ $user->name }}</p>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
            <span class="inline-block mt-1 text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">Franchise Admin</span>
        </div>
    </div>

    {{-- Branch details (read-only) --}}
    @if($franchise)
    <div class="bg-white rounded-2xl border border-border p-6">
        <h2 class="text-sm font-bold text-fran mb-4">Branch Details</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
                <dt class="text-xs text-gray-400">Branch Name</dt>
                <dd class="text-gray-800 font-medium">{{ $franchise->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Branch Code</dt>
                <dd class="text-gray-800 font-medium">{{ $franchise->franchise_code ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">City</dt>
                <dd class="text-gray-800 font-medium">{{ $franchise->city ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Status</dt>
                <dd>
                    <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium
                        {{ ($franchise->status ?? '') === 'active' ? 'bg-green-50 text-stu' : 'bg-gray-100 text-gray-500' }}">
                        {{ ucfirst($franchise->status ?? 'unknown') }}
                    </span>
                </dd>
            </div>
            @if(!empty($franchise->address))
            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400">Address</dt>
                <dd class="text-gray-800 font-medium">{{ $franchise->address }}</dd>
            </div>
            @endif
        </dl>
        <p class="text-xs text-gray-400 mt-4">Branch details are managed by the head office. Contact the administrator to change them.</p>
    </div>
    @endif

    {{-- Account details --}}
    <form method="POST" action="{{ route('franchise.profile.update') }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-border p-6 mb-4">
            <h2 class="text-sm font-bold text-fran mb-4">Account Details</h2>
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
            <h2 class="text-sm font-bold text-fran mb-1">Change Password</h2>
            <p class="text-xs text-gray-400 mb-4">Leave blank to keep your current password.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
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

    {{-- Sign out --}}
    <form method="POST" action="{{ route('franchise.logout') }}" class="pt-2">
        @csrf
        <button type="submit"
                class="px-5 py-2.5 border border-red-200 text-red-500 rounded-xl text-sm font-semibold hover:bg-red-50 transition-colors">
            Sign Out
        </button>
    </form>

</div>

@endsection
