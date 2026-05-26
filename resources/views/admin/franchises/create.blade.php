@extends('layouts.admin')
@section('title', 'Add New Franchise')
@section('page-title', 'Add New Franchise')

@section('content')

{{-- 3-step progress indicator --}}
<div class="flex items-center gap-0 mb-6">
    @foreach([1 => 'Basic Info', 2 => 'Documents', 3 => 'Review'] as $step => $label)
        <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $step === 1 ? 'bg-fran text-white' : 'bg-bg-mid text-gray-400' }}">
                    {{ $step }}
                </div>
                <span class="text-sm {{ $step === 1 ? 'text-fran font-semibold' : 'text-gray-400' }}">
                    Step {{ $step }}: {{ $label }}
                </span>
            </div>
            @if(!$loop->last)
                <div class="flex-1 h-px bg-border mx-4"></div>
            @endif
        </div>
    @endforeach
</div>

<div class="grid grid-cols-3 gap-6">

    {{-- Form --}}
    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.franchises.store') }}">
            @csrf

            {{-- Basic Information --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-1">Basic Information</h2>
                <div class="h-px bg-border mb-4"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Franchise / Academy Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="e.g. Kothrud Academy"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent @error('name') border-red-400 @enderror">
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Owner Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" required
                               placeholder="Full name as per documents"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent @error('owner_name') border-red-400 @enderror">
                        @error('owner_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               placeholder="owner@academy.in"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Mobile Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                               placeholder="+91 98765 43210"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent @error('phone') border-red-400 @enderror">
                        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp Number</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp') }}"
                               placeholder="Same as mobile"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Location Details --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-4">
                <h2 class="text-sm font-bold text-admin mb-1">Location Details</h2>
                <div class="h-px bg-border mb-4"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Full Address <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="address" value="{{ old('address') }}" required
                               placeholder="Flat/Office No., Building, Street, Area"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent @error('address') border-red-400 @enderror">
                        @error('address')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            City <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="city" value="{{ old('city') }}" required
                               placeholder="e.g. Pune"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent @error('city') border-red-400 @enderror">
                        @error('city')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pincode</label>
                        <input type="text" name="pincode" value="{{ old('pincode') }}"
                               placeholder="411 007"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                        <input type="text" name="state" value="{{ old('state', 'Maharashtra') }}"
                               placeholder="Maharashtra"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Business Details --}}
            <div class="bg-white rounded-2xl border border-border p-6 mb-6">
                <h2 class="text-sm font-bold text-admin mb-1">Business Details</h2>
                <div class="h-px bg-border mb-4"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">GST Number</label>
                        <input type="text" name="gst_number" value="{{ old('gst_number') }}"
                               placeholder="22AAAAA0000A1Z5"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">PAN Number</label>
                        <input type="text" name="pan_number" value="{{ old('pan_number') }}"
                               placeholder="ABCDE1234F"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Franchise Agreement Date</label>
                        <input type="date" name="agreed_at" value="{{ old('agreed_at') }}"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.franchises.index') }}"
                   class="px-5 py-2.5 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
                    Cancel
                </a>
                <button type="submit" name="draft" value="1"
                        class="px-5 py-2.5 border border-fran text-fran rounded-xl text-sm font-semibold hover:bg-fran-light transition-colors">
                    Save as Draft
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Save and Continue to Step 2 →
                </button>
            </div>
        </form>
    </div>

    {{-- Required Documents sidebar --}}
    <div class="bg-white rounded-2xl border border-border p-6 h-fit">
        <h3 class="text-sm font-bold text-admin mb-3">Required Documents</h3>
        <p class="text-xs text-gray-500 mb-3">Collect these before Step 2:</p>
        <ol class="space-y-2 text-sm text-gray-600">
            @foreach(['GST Certificate', 'PAN Card Copy', 'Aadhaar Card', 'Address Proof', 'Bank Details', 'Franchise Agreement'] as $i => $doc)
                <li class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-fran-light text-fran text-xs flex items-center justify-center font-bold flex-shrink-0">
                        {{ $i + 1 }}
                    </span>
                    {{ $doc }}
                </li>
            @endforeach
        </ol>
        <div class="mt-4 pt-4 border-t border-border">
            <p class="text-xs text-gray-400">Documents are uploaded in Step 2 after basic information is saved.</p>
        </div>
    </div>

</div>

@endsection
