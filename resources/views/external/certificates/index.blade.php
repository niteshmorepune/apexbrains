@extends('layouts.external')
@section('title', 'Certificate Vault')

@section('content')
@php
    $examCerts = $certificates->whereNotIn('type', ['competition']);
    $compCerts = $certificates->where('type', 'competition');
@endphp

<div x-data="{ view: 'menu' }">

    {{-- ===== E51: selector (Exam locked, Competition unlocked) ===== --}}
    <template x-if="view === 'menu'">
        <div>
            <x-student-header title="Certificate Vault" :back="route('external.home')" />
            <div class="px-4 pb-4 space-y-3">
                <div class="w-full bg-bg-light rounded-2xl border border-border p-4 flex items-center gap-3 opacity-60">
                    <span class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-2xl flex-shrink-0 grayscale">🎓</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-500">Exam</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Level completion certificates (internal students)</p>
                    </div>
                    <span class="ml-auto text-gray-400">🔒</span>
                </div>
                <button type="button" @click="view = 'competition'" class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-2xl flex-shrink-0">🏆</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Competition</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Awards and participation certificates</p>
                    </div>
                    <span class="ml-auto text-xs font-bold text-gray-400">{{ $compCerts->count() }}</span>
                </button>
            </div>
        </div>
    </template>

    {{-- ===== Competition certificate list ===== --}}
    <template x-if="view === 'competition'">
        <div>
            <div class="px-4 pt-5 pb-3 flex items-center gap-2">
                <button type="button" @click="view = 'menu'" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900">Competition Certificates</h1>
            </div>
            <div class="px-4 pb-4 space-y-3">
                @forelse($compCerts as $cert)
                    <div class="bg-white rounded-2xl border border-border p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-xl flex-shrink-0">🏆</span>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-800 text-sm truncate">{{ $cert->title ?? 'Competition Certificate' }}</p>
                                <p class="text-xs text-gray-400">{{ $cert->issued_at?->format('d M Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('external.certificates.show', $cert) }}" class="block w-full text-center text-sm bg-fran text-white py-2.5 rounded-xl font-bold">View Certificate</a>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
                        <div class="text-3xl mb-2">🏆</div>
                        <p class="text-sm font-medium">No competition certificates yet</p>
                        <p class="text-xs mt-1">Participate in competitions to earn certificates.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </template>

</div>
@endsection
