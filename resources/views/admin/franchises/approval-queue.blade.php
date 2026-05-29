@extends('layouts.admin')
@section('title', 'Franchise Approval Queue')
@section('page-title', 'Franchise Approval Queue')

@section('breadcrumb')
    <a href="{{ route('admin.franchises.index') }}" class="text-fran hover:underline">Franchises</a>
    <span class="mx-1 text-gray-400">/</span>
    <span>Approval Queue</span>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

<div class="flex items-center gap-3 mb-5">
    <h2 class="text-sm font-semibold text-gray-700">Pending Applications</h2>
    <span class="bg-logo-amber text-white text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $pending->count() }}</span>
</div>

@if($pending->isEmpty())
    <div class="bg-white rounded-2xl border border-border p-16 text-center text-gray-400">
        <div class="text-4xl mb-3">✅</div>
        <p class="font-medium text-gray-600">No pending franchise applications.</p>
        <p class="text-sm mt-1">All applications have been reviewed.</p>
    </div>
@else
    <div class="grid grid-cols-[1fr_320px] gap-5" x-data="{ selected: null, franchise: {} }">

        {{-- Left: applicant list --}}
        <div class="space-y-4">
            @foreach($pending as $f)
                <div class="bg-white rounded-2xl border border-border p-5 hover:border-fran transition-colors cursor-pointer"
                     :class="selected === {{ $f->id }} ? 'border-fran ring-1 ring-fran' : ''"
                     @click="selected = {{ $f->id }}; franchise = {{ json_encode([
                         'id'         => $f->id,
                         'name'       => $f->name,
                         'owner'      => $f->owner_name,
                         'city'       => $f->city,
                         'state'      => $f->state,
                         'email'      => $f->email,
                         'phone'      => $f->phone,
                         'applied'    => $f->created_at->format('d M Y'),
                         'gst'        => $f->gst_number,
                         'pan'        => $f->pan_number,
                     ]) }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-fran-light text-fran font-bold text-sm flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($f->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-admin text-sm">{{ $f->name }}</p>
                                <p class="text-xs text-gray-500">{{ $f->owner_name }} · {{ $f->city }}, {{ $f->state }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">Applied {{ $f->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="mt-3 flex items-center gap-3">
                        {{-- Doc status pills --}}
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $f->gst_number ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">GST</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $f->pan_number ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">PAN</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Agreement</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Bank</span>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.franchises.approve', $f) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-green-500 text-white text-xs font-semibold py-2 rounded-lg hover:bg-green-600 transition-colors">
                                Approve
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.franchises.reject', $f) }}" class="flex-1"
                              x-data="{ open: false }">
                            @csrf
                            <button type="button" @click="open = true"
                                    class="w-full bg-red-50 text-red-600 border border-red-200 text-xs font-semibold py-2 rounded-lg hover:bg-red-100 transition-colors">
                                Reject
                            </button>
                            <div x-show="open" x-transition class="mt-2">
                                <input type="text" name="reason" placeholder="Reason for rejection..."
                                       class="w-full border border-border rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-fran mb-1.5">
                                <button type="submit" class="w-full bg-red-500 text-white text-xs font-semibold py-1.5 rounded-lg hover:bg-red-600 transition-colors">
                                    Confirm Reject
                                </button>
                            </div>
                        </form>

                        <a href="{{ route('admin.franchises.show', $f) }}"
                           class="flex-1 text-center bg-bg-light text-gray-600 border border-border text-xs font-medium py-2 rounded-lg hover:bg-bg-mid transition-colors">
                            View Docs
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Right: document preview panel --}}
        <div class="bg-white rounded-2xl border border-border p-5 h-fit sticky top-5">
            <template x-if="selected === null">
                <div class="text-center text-gray-400 py-12">
                    <div class="text-3xl mb-2">👈</div>
                    <p class="text-sm">Select an application to preview documents</p>
                </div>
            </template>
            <template x-if="selected !== null">
                <div>
                    <h3 class="font-semibold text-admin text-sm mb-1" x-text="franchise.name"></h3>
                    <p class="text-xs text-gray-500 mb-4" x-text="franchise.owner + ' · ' + franchise.city"></p>

                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Document Status</p>
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">GST Certificate</span>
                            <span :class="franchise.gst ? 'text-green-600 font-medium' : 'text-red-500'" x-text="franchise.gst ? '✓ Uploaded' : '✗ Missing'"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">PAN Card</span>
                            <span :class="franchise.pan ? 'text-green-600 font-medium' : 'text-red-500'" x-text="franchise.pan ? '✓ Uploaded' : '✗ Missing'"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Aadhaar Card</span>
                            <span class="text-gray-400">— Pending</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Address Proof</span>
                            <span class="text-gray-400">— Pending</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Bank Details</span>
                            <span class="text-gray-400">— Pending</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Franchise Agreement</span>
                            <span class="text-gray-400">— Pending</span>
                        </div>
                    </div>

                    <hr class="my-4 border-border">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Details</p>
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="text-gray-700 text-xs" x-text="franchise.email"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Phone</span><span class="text-gray-700" x-text="franchise.phone"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Applied</span><span class="text-gray-700" x-text="franchise.applied"></span></div>
                    </div>
                </div>
            </template>
        </div>
    </div>
@endif

@endsection
