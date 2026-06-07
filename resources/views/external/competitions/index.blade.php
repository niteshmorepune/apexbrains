@extends('layouts.external')
@section('title', 'Competition')

@section('content')
@php $borderColors = ['#D42B2B', '#F5A623', '#FFD54F', '#FF69B4', '#1A73E8', '#9C27B0']; @endphp

<div x-data="{ view: 'menu' }">

    {{-- ===== E47: My Exams selector (Exam practice locked, Competition unlocked) ===== --}}
    <template x-if="view === 'menu'">
        <div>
            <x-student-header title="My Exams" :back="route('external.home')" />
            <div class="px-4 pb-4 space-y-3">
                <div class="w-full bg-bg-light rounded-2xl border border-border p-4 flex items-center gap-3 opacity-60">
                    <span class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-2xl flex-shrink-0 grayscale">📝</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-500">Exam practice</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Evaluate your calculation skills and track your learning progress</p>
                    </div>
                    <span class="ml-auto text-gray-400">🔒</span>
                </div>
                <button type="button" @click="view = 'list'" class="w-full bg-white rounded-2xl border border-border p-4 flex items-center gap-3 text-left">
                    <span class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-2xl flex-shrink-0">🏆</span>
                    <div class="min-w-0">
                        <p class="font-bold text-gray-800">Competition</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Challenge your abilities and compete with top performers</p>
                    </div>
                </button>
            </div>
        </div>
    </template>

    {{-- ===== E48: Competition list ===== --}}
    <template x-if="view === 'list'">
        <div>
            <div class="px-4 pt-5 pb-3 flex items-center gap-2">
                <button type="button" @click="view = 'menu'" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h1 class="flex-1 text-center pr-7 text-[17px] font-bold text-gray-900">Competition</h1>
            </div>
            <div class="px-4 pb-4 space-y-4">
                {{-- Upcoming --}}
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Upcoming Competitions</p>
                    @forelse($openCompetitions as $comp)
                        <a href="{{ route('external.competitions.show', $comp) }}" class="block bg-white rounded-2xl border border-border p-4 mb-3">
                            <div class="flex items-start gap-3">
                                <span class="w-10 h-10 rounded-xl bg-fran-light flex items-center justify-center text-fran flex-shrink-0">📅</span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 text-sm">{{ $comp->title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">@if($comp->start_date){{ $comp->start_date->format('d M Y') }} · @endif @if($comp->fee_amount > 0)₹{{ number_format($comp->fee_amount, 0) }}@else Free @endif</p>
                                </div>
                                @if(in_array($comp->id, $registeredIds))
                                    <span class="text-[11px] bg-stu-light text-stu px-2.5 py-1 rounded-full font-bold flex-shrink-0">Registered ✓</span>
                                @else
                                    <span class="text-[11px] bg-bg-mid text-gray-500 px-2.5 py-1 rounded-full font-medium flex-shrink-0">View</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="bg-white rounded-2xl border border-border p-6 text-center text-sm text-gray-400">No open competitions right now.</div>
                    @endforelse
                </div>

                {{-- Past --}}
                @if($pastCompetitions->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Past Competitions</p>
                        <div class="space-y-3">
                            @foreach($pastCompetitions as $i => $comp)
                                @php $participated = in_array($comp->id, $registeredIds); @endphp
                                <div class="bg-white rounded-2xl border border-border overflow-hidden flex items-stretch">
                                    <span class="w-1.5 flex-shrink-0" style="background-color: {{ $borderColors[$i % count($borderColors)] }}"></span>
                                    <div class="flex items-center gap-3 p-4 flex-1 min-w-0">
                                        <span class="w-10 h-10 rounded-full bg-bg-light flex items-center justify-center flex-shrink-0">📊</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-800 truncate">{{ $comp->title }}</p>
                                            <p class="text-xs text-gray-400">{{ $comp->end_date?->format('d M Y') }}@if($participated) · Participated @endif</p>
                                        </div>
                                        @if($participated)
                                            <a href="{{ route('external.competitions.show', $comp) }}" class="text-xs text-fran font-semibold flex-shrink-0">View Report</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </template>

</div>
@endsection
