@extends('layouts.student')
@section('title', 'Competition')

@section('content')
@php $borderColors = ['#D42B2B', '#F5A623', '#FFD54F', '#FF69B4', '#1A73E8', '#9C27B0']; @endphp

<x-student-header title="Competition" :back="route('student.exams.index')" />

<div class="px-4 pb-4 space-y-4">

    {{-- Upcoming --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Upcoming Competitions</p>
        @forelse($competitions as $comp)
            <div class="bg-white rounded-2xl border border-border p-4 mb-3">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 rounded-xl bg-fran-light flex items-center justify-center text-fran flex-shrink-0">📅</span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-800 text-sm">{{ $comp->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if($comp->start_date){{ $comp->start_date->format('d M Y') }} · @endif
                            @if($comp->fee_amount > 0)₹{{ number_format($comp->fee_amount, 0) }}@else Free @endif
                        </p>
                    </div>
                    @if(in_array($comp->id, $myRegistrationIds))
                        <a href="{{ route('student.competitions.show', $comp) }}" class="text-[11px] bg-stu-light text-stu px-2.5 py-1 rounded-full font-bold flex-shrink-0">Registered ✓</a>
                    @else
                        <a href="{{ route('student.competitions.show', $comp) }}" class="text-[11px] bg-bg-mid text-gray-500 px-2.5 py-1 rounded-full font-medium flex-shrink-0">View</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-border p-6 text-center text-sm text-gray-400">No open competitions right now.</div>
        @endforelse
    </div>

    {{-- Completed / Past --}}
    @if($pastCompetitions->isNotEmpty())
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Completed Competitions</p>
            <div class="space-y-3">
                @foreach($pastCompetitions as $i => $comp)
                    @php
                        $registered = in_array($comp->id, $myRegistrationIds);
                        $completed  = in_array($comp->id, $mySubmittedCompetitionIds);
                    @endphp
                    <div class="bg-white rounded-2xl border border-border overflow-hidden flex items-stretch">
                        <span class="w-1.5 flex-shrink-0" style="background-color: {{ $borderColors[$i % count($borderColors)] }}"></span>
                        <div class="flex items-center gap-3 p-4 flex-1 min-w-0">
                            <span class="w-10 h-10 rounded-full bg-bg-light flex items-center justify-center flex-shrink-0">📊</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800 truncate">{{ $comp->title }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $comp->end_date?->format('d M Y') }}
                                    @if($completed) · Completed ✓ @elseif($registered) · Participated @endif
                                </p>
                            </div>
                            @if($completed)
                                <a href="{{ route('student.competitions.result', $comp) }}" class="text-xs text-fran font-semibold flex-shrink-0">View Report</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
