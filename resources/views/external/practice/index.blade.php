@extends('layouts.external')
@section('title', 'Practice')

@section('content')
<div x-data="{ view: 'menu' }">

    {{-- ===== E41: type selector (Class/Exam locked, Competition unlocked) ===== --}}
    <template x-if="view === 'menu'">
        <div>
            <x-student-header title="Practice" :back="route('external.home')" />
            <div class="px-4 pb-4 space-y-3">
                {{-- Locked options --}}
                @foreach([['📘','Class Practice','Build speed, accuracy, and confidence through guided exercises'],['📝','Exam Practice','Evaluate your calculation skills and track your learning progress']] as [$emoji,$t,$sub])
                    <div class="w-full bg-bg-light rounded-2xl border border-border p-4 flex items-center gap-3 opacity-60">
                        <span class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-2xl flex-shrink-0 grayscale">{{ $emoji }}</span>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-500">{{ $t }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 leading-snug">{{ $sub }}</p>
                        </div>
                        <span class="ml-auto text-gray-400">🔒</span>
                    </div>
                @endforeach
                {{-- Unlocked --}}
                <button type="button" @click="view = 'papers'" class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-2xl flex-shrink-0">🏆</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Competition Practice</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Challenge your abilities and compete with top performers</p>
                    </div>
                </button>
            </div>
        </div>
    </template>

    {{-- ===== Competition practice papers list ===== --}}
    <template x-if="view === 'papers'">
        <div>
            <div class="px-4 pt-5 pb-3 flex items-center gap-2">
                <button type="button" @click="view = 'menu'" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900">Competition Practice</h1>
            </div>
            <div class="px-4 pb-4 space-y-3">
                <div class="bg-fran-light rounded-2xl p-4">
                    <p class="text-sm font-bold text-fran">Competition Practice Papers</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $papers->count() }} papers available — practise to prepare</p>
                </div>

                @forelse($papers as $paper)
                    @php $myAttempt = $attemptMap->get($paper->id); @endphp
                    <div class="bg-white rounded-2xl border border-border p-4">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 rounded-xl bg-fran flex items-center justify-center text-white font-black text-sm flex-shrink-0">{{ $paper->paper_number }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-800 text-sm">{{ $paper->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $paper->total_questions }}Q · {{ $paper->duration_minutes }}min @if($paper->difficulty) · <span class="capitalize">{{ $paper->difficulty }}</span>@endif</p>
                            </div>
                            @if($myAttempt)
                                <div class="text-right flex-shrink-0"><p class="text-sm font-black text-fran">{{ number_format($myAttempt->percentage, 0) }}%</p><p class="text-[11px] text-gray-400">Best</p></div>
                            @endif
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            @if($myAttempt)
                                <a href="{{ route('external.practice.result', $paper) }}" class="flex-1 text-center text-xs border border-border text-gray-600 py-2 rounded-xl">View Result</a>
                            @endif
                            <form method="POST" action="{{ route('external.practice.start', $paper) }}" class="{{ $myAttempt ? '' : 'flex-1' }}">
                                @csrf
                                <button type="submit" class="w-full text-xs bg-fran text-white py-2 px-4 rounded-xl font-bold">{{ $myAttempt ? 'Retry' : 'Start →' }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400"><p class="text-sm">No practice papers available yet.</p></div>
                @endforelse
            </div>
        </div>
    </template>

</div>
@endsection
