@extends('layouts.external')
@section('title', 'Practice Papers')

@section('content')
<div class="p-4 space-y-3">

    <div class="bg-fran/10 rounded-2xl p-4">
        <p class="text-sm font-semibold text-fran">Competition Practice Papers</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $papers->count() }} papers available — practise to prepare for competitions</p>
    </div>

    @forelse($papers as $paper)
        @php $myAttempt = $attemptMap->get($paper->id); @endphp
        <div class="bg-white rounded-2xl border border-border p-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-fran flex items-center justify-center text-white font-black text-sm flex-shrink-0">
                    {{ $paper->paper_number }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm">{{ $paper->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $paper->total_questions }}Q · {{ $paper->duration_minutes }}min
                        @if($paper->difficulty) · <span class="capitalize">{{ $paper->difficulty }}</span>@endif
                    </p>
                </div>
                @if($myAttempt)
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-black text-fran">{{ number_format($myAttempt->percentage, 0) }}%</p>
                        <p class="text-xs text-gray-400">Best</p>
                    </div>
                @endif
            </div>

            <div class="mt-3 flex items-center gap-2">
                @if($myAttempt)
                    <a href="{{ route('external.practice.result', $paper) }}"
                       class="flex-1 text-center text-xs border border-border text-gray-600 py-2 rounded-xl hover:bg-bg-light">
                        View Result
                    </a>
                @endif
                <form method="POST" action="{{ route('external.practice.start', $paper) }}"
                      class="{{ $myAttempt ? '' : 'flex-1' }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-xs bg-fran text-white py-2 px-4 rounded-xl font-medium hover:bg-fran-dark">
                        {{ $myAttempt ? 'Retry' : 'Start →' }}
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-border p-10 text-center text-gray-400">
            <p class="text-sm">No practice papers available yet.</p>
        </div>
    @endforelse

</div>
@endsection
