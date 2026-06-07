@extends('layouts.external')
@section('title', 'Certificate')

@section('content')
<x-student-header title="Certificate" :back="route('external.certificates.index')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Certificate preview --}}
    <div class="bg-white rounded-2xl border-2 border-pink-300 p-6 shadow-sm">
        <div class="text-center mb-4">
            <p class="font-black text-sm"><span class="text-logo-red">Apex</span> <span class="text-fran">Brains</span></p>
            <p class="text-[10px] text-gray-400 tracking-widest uppercase mt-2">Certificate of Achievement</p>
        </div>

        <p class="text-center text-xs text-gray-400 mb-1">This is to certify that</p>
        <p class="text-center text-xl font-black text-gray-900 mb-1">{{ $certificate->student?->full_name }}</p>
        <p class="text-center text-xs text-gray-400 mb-1">has successfully participated in</p>
        <p class="text-center text-base font-bold text-fran mb-3">{{ $certificate->title ?? 'the Competition' }}</p>

        <p class="text-center text-[11px] text-gray-400 mb-4">Issued: {{ $certificate->issued_at?->format('F Y') }} · ID: {{ $certificate->certificate_number }}</p>

        <div class="flex justify-center my-3">
            {!! QrCode::size(80)->generate(route('certificate.verify', $certificate->verification_code)) !!}
        </div>
        <p class="text-center text-[10px] text-gray-300">Scan to verify</p>
    </div>

    {{-- Actions --}}
    <div class="space-y-3">
        <a href="{{ route('external.certificates.pdf', $certificate) }}" class="block w-full py-3.5 bg-fran text-white rounded-2xl text-sm font-bold text-center">Download as PDF</a>
        @php $whatsapp = $certificate->student?->primaryParent?->whatsapp ?? $certificate->student?->parent_whatsapp; @endphp
        @if($whatsapp)
            <a href="https://wa.me/91{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('🏆 Certificate — ' . ($certificate->student?->full_name) . ' | ' . ($certificate->title ?? 'Competition') . ' | Apex Brains | Verify: ' . route('certificate.verify', $certificate->verification_code)) }}"
               target="_blank" class="block w-full py-3.5 border border-stu text-stu rounded-2xl text-sm font-bold text-center">Share via WhatsApp</a>
        @endif
        <button onclick="window.print()" class="block w-full py-3.5 border border-border text-gray-600 rounded-2xl text-sm font-bold text-center">Print Certificate</button>
    </div>

</div>
@endsection
