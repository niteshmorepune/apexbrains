@extends('layouts.student')
@section('title', 'My Certificates')

@section('content')
<div class="p-4 space-y-3">

    @forelse($certificates as $cert)
        <div class="bg-white rounded-2xl border-2 border-fran p-4">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-xl bg-fran flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-black text-sm">L{{ $cert->level?->number }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm">
                        Level {{ $cert->level?->number }}
                        @if($cert->level?->title) — {{ $cert->level->title }}@endif
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5 capitalize">
                        {{ str_replace('_', ' ', $cert->type) }} Certificate
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Issued {{ $cert->issued_at?->format('d M Y') }}
                        @if($cert->issuedBy) · {{ $cert->issuedBy->name }}@endif
                    </p>
                    <p class="font-mono text-xs text-gray-300 mt-1">{{ $cert->certificate_number }}</p>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <a href="{{ route('student.certificates.download', $cert) }}"
                   class="flex-1 text-center text-xs bg-fran text-white py-2 rounded-xl font-medium hover:bg-fran-dark">
                    View / Print
                </a>
                <a href="{{ route('certificate.verify', $cert->verification_code) }}"
                   target="_blank"
                   class="text-xs border border-border text-gray-500 px-3 py-2 rounded-xl hover:bg-bg-light">
                    Verify
                </a>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-border p-12 text-center text-gray-400">
            <div class="w-14 h-14 bg-bg-mid rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <p class="text-sm font-medium">No certificates yet</p>
            <p class="text-xs mt-1">Complete a level exam to earn your first certificate.</p>
        </div>
    @endforelse

    {{-- Locked / future certificates --}}
    @if($lockedLevels->isNotEmpty())
        @if($certificates->isNotEmpty())
            <p class="text-xs text-gray-400 font-medium pt-2">Upcoming Levels</p>
        @endif
        @foreach($lockedLevels as $level)
            <div class="relative bg-white rounded-2xl border-2 border-dashed border-border p-4 opacity-50 select-none pointer-events-none" aria-hidden="true">
                <div class="absolute inset-0 rounded-2xl backdrop-blur-[1px] flex items-center justify-center z-10">
                    <div class="bg-white/80 rounded-xl px-4 py-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs font-semibold text-gray-500">Locked</span>
                    </div>
                </div>
                <div class="flex items-start gap-3 blur-[2px]">
                    <div class="w-12 h-12 rounded-xl bg-gray-200 flex items-center justify-center flex-shrink-0">
                        <span class="text-gray-400 font-black text-sm">L{{ $level->number }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-400 text-sm">Level {{ $level->number }}@if($level->title) — {{ $level->title }}@endif</p>
                        <p class="text-xs text-gray-300 mt-0.5">Level Completion Certificate</p>
                    </div>
                </div>
                <div class="mt-3 h-8 bg-gray-100 rounded-xl blur-[2px]"></div>
            </div>
        @endforeach
    @endif

</div>
@endsection
