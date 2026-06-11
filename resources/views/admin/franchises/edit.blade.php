@extends('layouts.admin')
@section('title', 'Edit — ' . $franchise->name)
@section('page-title', 'Edit Franchise')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.franchises.update', $franchise) }}">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-1">Basic Information</h2>
                <div class="h-px bg-border mb-4"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Academy Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $franchise->name) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('name') border-red-400 @enderror">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Owner Name <span class="text-red-500">*</span></label>
                        <input type="text" name="owner_name" value="{{ old('owner_name', $franchise->owner_name) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('owner_name') border-red-400 @enderror">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $franchise->email) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('email') border-red-400 @enderror">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $franchise->phone) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('phone') border-red-400 @enderror">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $franchise->whatsapp) }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-1">Location</h2>
                <div class="h-px bg-border mb-4"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Address <span class="text-red-500">*</span></label>
                        <input type="text" name="address" value="{{ old('address', $franchise->address) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('address') border-red-400 @enderror">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">City <span class="text-red-500">*</span></label>
                        <input type="text" name="city" value="{{ old('city', $franchise->city) }}" required
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pincode</label>
                        <input type="text" name="pincode" value="{{ old('pincode', $franchise->pincode) }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                        <input type="text" name="state" value="{{ old('state', $franchise->state) }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6 mb-6">
                <h2 class="text-sm font-bold text-admin mb-1">Business</h2>
                <div class="h-px bg-border mb-4"></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">GST Number</label>
                        <input type="text" name="gst_number" value="{{ old('gst_number', $franchise->gst_number) }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">PAN Number</label>
                        <input type="text" name="pan_number" value="{{ old('pan_number', $franchise->pan_number) }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.franchises.show', $franchise) }}"
                   class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-border p-5 h-fit">
        <h3 class="text-sm font-bold text-admin mb-3">Franchise Info</h3>
        <div class="space-y-2 text-sm text-gray-600">
            <div class="flex justify-between">
                <span class="text-gray-400">Code</span>
                <span class="font-mono">{{ $franchise->franchise_code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Franchise No.</span>
                <span class="font-mono">{{ $franchise->franchise_number ? str_pad($franchise->franchise_number, 2, '0', STR_PAD_LEFT) : '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Status</span>
                <x-status-badge :status="$franchise->status" />
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Joined</span>
                <span>{{ $franchise->created_at->format('d M Y') }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
