@extends('layouts.external')
@section('title', 'Certificate Vault')

@section('content')
<div class="p-4 space-y-3" x-data="{ tab: 'competition' }">

    <h1 class="text-lg font-bold text-admin">Certificate Vault</h1>

    {{-- Type tabs --}}
    <div class="flex gap-2">
        <button @click="tab = 'exam'"
                :class="tab === 'exam' ? 'bg-fran text-white' : 'bg-white border border-border text-gray-600'"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            Exam
        </button>
        <button @click="tab = 'competition'"
                :class="tab === 'competition' ? 'bg-fran text-white' : 'bg-white border border-border text-gray-600'"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            Competition
        </button>
    </div>

    {{-- Exam certificates --}}
    <div x-show="tab === 'exam'" class="space-y-3">
        @php $examCerts = $certificates->whereNotIn('type', ['competition']); @endphp
        @forelse($examCerts as $cert)
            <div class="bg-white rounded-2xl border border-border p-4 hover:border-fran transition-colors">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-fran/10 flex items-center justify-center flex-shrink-0">
                        <span class="text-fran font-black text-sm">{{ $cert->level ? 'L'.$cert->level->number : '📜' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate">{{ $cert->title ?? 'Certificate' }}</p>
                        <p class="text-xs text-gray-400">{{ $cert->issued_at?->format('d M Y') }}</p>
                    </div>
                </div>
                <a href="{{ route('external.certificates.show', $cert) }}"
                   class="block w-full text-center text-xs bg-fran text-white py-2 rounded-xl font-medium hover:bg-fran-dark transition-colors">
                    Download
                </a>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
                <div class="text-3xl mb-2">📜</div>
                <p class="text-sm font-medium">No exam certificates</p>
            </div>
        @endforelse
    </div>

    {{-- Competition certificates --}}
    <div x-show="tab === 'competition'" class="space-y-3">
        @php $compCerts = $certificates->where('type', 'competition'); @endphp
        @forelse($compCerts as $cert)
            <div class="bg-white rounded-2xl border border-border p-4 hover:border-fran transition-colors">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                        <span class="text-xl">🏆</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate">{{ $cert->title ?? 'Competition Certificate' }}</p>
                        <p class="text-xs text-gray-400">{{ $cert->issued_at?->format('d M Y') }}</p>
                    </div>
                </div>
                <a href="{{ route('external.certificates.show', $cert) }}"
                   class="block w-full text-center text-xs bg-fran text-white py-2 rounded-xl font-medium hover:bg-fran-dark transition-colors">
                    Download
                </a>
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
@endsection
