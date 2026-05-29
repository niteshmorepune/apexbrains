@extends('layouts.student')
@section('title', 'Competitions')

@section('content')
<div class="p-4 space-y-4">

    {{-- Upcoming Exams --}}
    <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Upcoming Exams</p>

        @if(session('success'))
            <div class="mb-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @forelse($competitions as $comp)
            <div class="bg-white rounded-2xl border border-border p-4 mb-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800 text-sm">{{ $comp->title }}</p>
                        @if($comp->description)
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $comp->description }}</p>
                        @endif
                        <div class="flex flex-wrap gap-2 mt-2">
                            @if($comp->start_date)
                                <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full">
                                    {{ $comp->start_date->format('d M') }}
                                </span>
                            @endif
                            @if($comp->registration_deadline)
                                <span class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full">
                                    Register by {{ $comp->registration_deadline->format('d M') }}
                                </span>
                            @endif
                            @if($comp->fee_amount > 0)
                                <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full">
                                    ₹{{ number_format($comp->fee_amount, 0) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if(in_array($comp->id, $myRegistrationIds))
                        <span class="text-xs bg-green-50 text-green-600 px-3 py-1.5 rounded-xl font-medium flex-shrink-0">
                            Registered ✓
                        </span>
                    @else
                        <form method="POST" action="{{ route('student.competitions.register', $comp) }}" class="flex-shrink-0">
                            @csrf
                            <button type="submit"
                                    class="text-xs bg-fran text-white px-3 py-1.5 rounded-xl font-medium hover:bg-fran-dark">
                                Register
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-border p-8 text-center text-gray-400">
                <p class="text-sm">No open competitions right now.</p>
                <p class="text-xs mt-1">Check back soon or practice with past papers.</p>
            </div>
        @endforelse
    </div>

    {{-- Past Exams --}}
    @if($pastCompetitions->isNotEmpty())
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Past Exams</p>
            <div class="bg-white rounded-2xl border border-border overflow-hidden">
                <div class="divide-y divide-border">
                    @foreach($pastCompetitions as $comp)
                        @php
                            $registered = in_array($comp->id, $myRegistrationIds);
                        @endphp
                        <div class="px-4 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center flex-shrink-0">
                                <span class="text-sm">🏆</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ $comp->title }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $comp->end_date?->format('d M Y') }}
                                    @if($registered)· <span class="text-stu font-medium">Participated</span>@endif
                                </p>
                            </div>
                            @if($registered)
                                <a href="{{ route('student.competitions.index') }}"
                                   class="text-xs text-fran hover:underline font-medium flex-shrink-0">
                                    View Report
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
