@extends('layouts.admin')
@section('title', $competition->title)
@section('page-title', $competition->title)

@section('page-actions')
    <a href="{{ route('admin.competitions.edit', $competition) }}"
       class="px-4 py-2 border border-border text-gray-600 text-sm font-semibold rounded-xl hover:bg-bg-light transition-colors">
        Edit
    </a>
@endsection

@section('content')

<div class="grid grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Registrations</p>
        <p class="text-2xl font-bold text-fran">{{ $competition->registrations_count }}</p>
        @if($competition->max_participants)
            <p class="text-xs text-gray-400 mt-1">of {{ $competition->max_participants }} max</p>
        @endif
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Entry Fee</p>
        <p class="text-2xl font-bold text-logo-amber">₹{{ number_format($competition->fee_amount) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Status</p>
        <p class="text-2xl font-bold {{ $competition->is_active ? 'text-stu' : 'text-gray-400' }}">
            {{ $competition->is_active ? 'Active' : 'Inactive' }}
        </p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-border p-6">
    <h2 class="text-sm font-semibold text-admin mb-4">Details</h2>
    <dl class="grid grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500 mb-0.5">Type</dt><dd class="capitalize font-medium">{{ $competition->competition_type }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Open to External</dt><dd>{{ $competition->is_open_to_external ? 'Yes' : 'No' }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Registration Deadline</dt><dd>{{ $competition->registration_deadline->format('d M Y') }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Start Date</dt><dd>{{ $competition->start_date->format('d M Y') }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">End Date</dt><dd>{{ $competition->end_date->format('d M Y') }}</dd></div>
        @if($competition->description)
            <div class="col-span-2"><dt class="text-gray-500 mb-0.5">Description</dt><dd>{{ $competition->description }}</dd></div>
        @endif
    </dl>
</div>

@endsection
