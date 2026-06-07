@extends('layouts.student')
@section('title', 'Certificate Vault')

@section('content')
@php
    $examCerts = $certificates->whereNotIn('type', ['competition']);
    $compCerts = $certificates->where('type', 'competition');
@endphp

<div x-data="{ view: 'menu' }">

    {{-- ===== S53: type selector ===== --}}
    <template x-if="view === 'menu'">
        <div>
            <x-student-header title="Certificate Vault" :back="route('student.home')" />
            <div class="px-4 pb-4 space-y-3">
                <button type="button" @click="view = 'exam'" class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-stu-light flex items-center justify-center text-2xl flex-shrink-0">🎓</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Exam</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Level completion certificates from your assessments</p>
                    </div>
                    <span class="ml-auto text-xs font-bold text-gray-400">{{ $examCerts->count() }}</span>
                </button>
                <button type="button" @click="view = 'competition'" class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-2xl flex-shrink-0">🏆</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Competition</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Awards and certificates from competitions</p>
                    </div>
                    <span class="ml-auto text-xs font-bold text-gray-400">{{ $compCerts->count() }}</span>
                </button>
            </div>
        </div>
    </template>

    {{-- ===== certificate list (exam / competition) ===== --}}
    @foreach(['exam' => $examCerts, 'competition' => $compCerts] as $kind => $certs)
        <template x-if="view === '{{ $kind }}'">
            <div>
                <div class="px-4 pt-5 pb-3 flex items-center gap-2">
                    <button type="button" @click="view = 'menu'" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900">{{ ucfirst($kind) }} Certificates</h1>
                </div>
                <div class="px-4 pb-4 space-y-3">
                    @forelse($certs as $cert)
                        <div class="bg-white rounded-2xl border border-border p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="w-11 h-11 rounded-xl {{ $kind === 'competition' ? 'bg-amber-50' : 'bg-stu-light' }} flex items-center justify-center text-xl flex-shrink-0">{{ $kind === 'competition' ? '🏆' : '🎓' }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 text-sm truncate">
                                        @if($cert->level) Level {{ $cert->level->number }}@if($cert->level->title) — {{ $cert->level->title }}@endif
                                        @else {{ $cert->title ?? 'Certificate' }} @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $cert->issued_at?->format('d M Y') }}</p>
                                </div>
                            </div>
                            <a href="{{ route('student.certificates.show', $cert) }}" class="block w-full text-center text-sm bg-fran text-white py-2.5 rounded-xl font-bold">View Certificate</a>
                        </div>
                    @empty
                        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
                            <div class="text-3xl mb-2">{{ $kind === 'competition' ? '🏆' : '🎓' }}</div>
                            <p class="text-sm font-medium">No {{ $kind }} certificates yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </template>
    @endforeach

</div>
@endsection
