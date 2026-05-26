@extends('layouts.franchise')
@section('title', 'Certificate ' . $certificate->certificate_number)
@section('page-title', 'Certificate')

@section('page-actions')
    <button onclick="window.print()" class="px-4 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50">Print / Download</button>
    <a href="{{ route('franchise.certificates.index') }}" class="px-4 py-2 border border-white text-white rounded-xl text-sm hover:bg-blue-600 transition-colors">← Certificates</a>
@endsection

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border-4 border-fran p-10 text-center" id="certificate">
        <div class="border-2 border-blue-200 rounded-xl p-8">
            <div class="w-14 h-14 rounded-full bg-logo-red mx-auto mb-4 flex items-center justify-center text-white font-black text-xl">AB</div>
            <p class="text-xs text-gray-400 tracking-widest uppercase mb-1">Apex Brains Academy</p>
            <p class="text-xs text-gray-300 mb-6">ISO 9001:2015 Certified</p>

            <p class="text-sm text-gray-500 mb-2">This is to certify that</p>
            <h1 class="text-3xl font-black text-fran mb-2">{{ $certificate->student?->full_name }}</h1>
            <p class="text-sm text-gray-500 mb-6">has successfully completed</p>

            <div class="bg-blue-50 rounded-xl py-3 px-6 inline-block mb-6">
                <p class="text-lg font-bold text-fran">
                    {{ $certificate->level ? 'Level ' . $certificate->level->number . ' — ' . $certificate->level->title : 'Abacus Programme' }}
                </p>
                <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $certificate->type) }} Certificate</p>
            </div>

            <p class="text-xs text-gray-400 mb-8">Issued on {{ $certificate->issued_at?->format('d F Y') }}</p>

            <div class="flex items-end justify-between">
                <div class="text-center">
                    <div class="w-24 border-b border-gray-300 mb-1 mx-auto"></div>
                    <p class="text-xs text-gray-400">{{ $certificate->issuedBy?->name ?? 'Branch Manager' }}</p>
                    <p class="text-xs text-gray-300">Authorized Signatory</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-bg-mid rounded-lg flex items-center justify-center mx-auto mb-1">
                        <span class="text-xs text-gray-400">QR</span>
                    </div>
                    <p class="text-xs text-gray-300">Scan to verify</p>
                </div>
                <div class="text-center">
                    <div class="w-24 border-b border-gray-300 mb-1 mx-auto"></div>
                    <p class="text-xs text-gray-400">Principal</p>
                    <p class="text-xs text-gray-300">Apex Brains Academy</p>
                </div>
            </div>

            <p class="text-xs text-gray-300 mt-6 font-mono">{{ $certificate->certificate_number }}</p>
        </div>
    </div>
</div>

@endsection
