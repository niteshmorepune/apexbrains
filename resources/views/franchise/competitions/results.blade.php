@extends('layouts.franchise')
@section('title', 'Competition Results — ' . $competition->title)
@section('page-title', 'Competition Results')

@section('content')

<nav class="flex items-center gap-2 text-[13px] text-gray-400 mb-1">
    <a href="{{ route('franchise.competitions.index') }}" class="hover:text-gray-600">Competitions</a>
    <span>/</span>
    <span class="font-semibold text-gray-700">{{ $competition->title }}</span>
</nav>
<h1 class="text-[22px] font-extrabold text-gray-900 mb-1">{{ $competition->title }} — Results</h1>
<p class="text-sm text-gray-400 mb-5">
    Results declared {{ $competition->results_declared_at?->format('d M Y, h:i A') }}.
    Champion = level-wise rank 1–3. Winner = level-wise rank 4–6.
</p>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ session('error') }}</div>
@endif

@forelse($results as $r)
    @php
        $level = $r['level'];
        $ranked = $r['ranked'];
        $issued = $r['issued'];
        $champions = $ranked->filter(fn ($a) => $a->rank <= 3);
        $winners   = $ranked->filter(fn ($a) => $a->rank >= 4 && $a->rank <= 6);
    @endphp
    <div class="bg-white rounded-2xl border border-border overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between flex-wrap gap-3">
            <h2 class="text-sm font-bold text-gray-800">{{ $level->title }}</h2>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('franchise.competitions.results.generate', [$competition, $level, 'champion']) }}">
                    @csrf
                    <button type="submit" @if($champions->isEmpty()) disabled @endif
                            class="px-4 py-2 rounded-xl text-xs font-semibold {{ $champions->isEmpty() ? 'bg-gray-100 text-gray-400' : 'bg-amber-500 text-white hover:bg-amber-600' }}">
                        Generate Champion Certificates
                    </button>
                </form>
                <form method="POST" action="{{ route('franchise.competitions.results.generate', [$competition, $level, 'winner']) }}">
                    @csrf
                    <button type="submit" @if($winners->isEmpty()) disabled @endif
                            class="px-4 py-2 rounded-xl text-xs font-semibold {{ $winners->isEmpty() ? 'bg-gray-100 text-gray-400' : 'bg-fran text-white hover:bg-fran-dark' }}">
                        Generate Winner Certificates
                    </button>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-500">
                        <th class="px-5 py-2.5 font-semibold">Rank</th>
                        <th class="px-4 py-2.5 font-semibold">Student</th>
                        <th class="px-4 py-2.5 font-semibold">Score</th>
                        <th class="px-4 py-2.5 font-semibold">Time Taken</th>
                        <th class="px-4 py-2.5 font-semibold">Submitted</th>
                        <th class="px-4 py-2.5 font-semibold">Certificate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($ranked as $attempt)
                        @php $issuedType = $issued[$attempt->student_id] ?? null; @endphp
                        <tr class="hover:bg-bg-light">
                            <td class="px-5 py-3 font-bold text-gray-700">
                                @if($attempt->rank <= 3)
                                    <span class="text-amber-500">#{{ $attempt->rank }}</span>
                                @elseif($attempt->rank <= 6)
                                    <span class="text-fran">#{{ $attempt->rank }}</span>
                                @else
                                    #{{ $attempt->rank }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-800">{{ $attempt->student?->full_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $attempt->percentage }}%</td>
                            <td class="px-4 py-3 text-gray-500">{{ $attempt->time_taken ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $attempt->submitted_at_ist?->format('d M Y, g:i A') }}</td>
                            <td class="px-4 py-3">
                                @if($issuedType === 'champion')
                                    <span class="text-xs bg-amber-50 text-amber-600 px-2.5 py-1 rounded-full font-medium">Champion issued</span>
                                @elseif($issuedType === 'winner')
                                    <span class="text-xs bg-blue-50 text-fran px-2.5 py-1 rounded-full font-medium">Winner issued</span>
                                @elseif($attempt->rank <= 6)
                                    <span class="text-xs text-gray-400">Not yet generated</span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="bg-white rounded-2xl border border-border px-5 py-16 text-center text-gray-400">
        <p class="text-base">No submitted attempts from your franchise's students in this competition yet.</p>
    </div>
@endforelse

@endsection
