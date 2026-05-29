@extends('layouts.student')
@section('title', 'Level ' . ($certificate->level?->number ?? '') . ' Certificate')

@section('content')
<div class="p-4 space-y-4">

    {{-- Certificate preview --}}
    <div id="cert-preview" class="bg-white rounded-2xl border-2 border-fran p-6">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-border">
            <div class="w-10 h-10 rounded-xl bg-logo-red flex items-center justify-center flex-shrink-0">
                <span class="text-white font-black text-xs">AB</span>
            </div>
            <div class="flex-1">
                <p class="font-black text-admin text-sm leading-tight">Apex Brains Academy</p>
                <p class="text-xs text-gray-400">ISO 9001:2015 Certified</p>
            </div>
        </div>

        <p class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Certificate of Completion</p>

        <p class="text-center text-sm text-gray-600 mb-1">This is to certify that</p>
        <p class="text-center text-xl font-black text-admin mb-1">{{ $certificate->student?->full_name }}</p>
        <p class="text-center text-sm text-gray-600 mb-3">
            has successfully completed
            @if($certificate->level)
                <span class="font-semibold">Level {{ $certificate->level->number }}
                @if($certificate->level->title) — {{ $certificate->level->title }}@endif</span>
            @else
                <span class="font-semibold">{{ $certificate->title }}</span>
            @endif
            @if($certificate->issuedBy?->franchise)
                at <span class="font-semibold">{{ $certificate->issuedBy->franchise->name }}, {{ $certificate->issuedBy->franchise->city }}</span>
            @endif
        </p>

        <p class="text-center text-xs text-gray-400 mb-5">
            Issued: {{ $certificate->issued_at?->format('F Y') }}
            &nbsp;·&nbsp; ID: {{ $certificate->certificate_number }}
        </p>

        {{-- Footer --}}
        <div class="flex items-end justify-between pt-4 border-t border-border">
            <div>
                <p class="text-xs text-gray-500">Received by: {{ $certificate->issuedBy?->name ?? 'Apex Brains Academy' }}</p>
                <p class="text-xs text-gray-300 mt-2 border-t border-gray-200 pt-1 w-32">Signature</p>
            </div>
            <div class="text-center">
                {!! QrCode::size(72)->generate(route('certificate.verify', $certificate->verification_code)) !!}
                <p class="text-xs text-gray-400 mt-1">Scan to verify</p>
            </div>
        </div>

        <p class="text-center text-xs text-gray-300 mt-4">This is a computer-generated certificate.</p>
    </div>

    {{-- Action buttons --}}
    <div class="space-y-3">
        <button onclick="window.print()"
                class="block w-full py-3 border border-border text-gray-600 rounded-2xl text-sm font-semibold text-center hover:bg-bg-light transition-colors">
            Print Certificate
        </button>

        <a href="{{ route('student.certificates.pdf', $certificate) }}"
           class="block w-full py-3 bg-stu text-white rounded-2xl text-sm font-semibold text-center hover:bg-stu-dark transition-colors">
            Download as PDF
        </a>

        @php $whatsapp = $certificate->student?->primaryParent?->whatsapp ?? $certificate->student?->parent_whatsapp; @endphp
        @if($whatsapp)
            <a href="https://wa.me/91{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('🎓 Certificate of Completion — ' . ($certificate->student?->full_name) . ' | Level ' . ($certificate->level?->number) . ' | Apex Brains Academy | Verify: ' . route('certificate.verify', $certificate->verification_code)) }}"
               target="_blank"
               class="block w-full py-3 bg-green-500 text-white rounded-2xl text-sm font-semibold text-center hover:bg-green-600 transition-colors">
                Share via WhatsApp
            </a>
        @endif
    </div>

    <a href="{{ route('student.certificates.index') }}"
       class="block text-center text-sm text-gray-400 hover:text-gray-600 py-2">
        ← Back to Vault
    </a>

</div>
@endsection
