{{-- Competition Exam window status pill: Not Started / Active / Ended.
     Status comes from Competition::examStatus() — the same check the
     controllers enforce — so what the student sees matches what the server allows. --}}
@php
    $statusClass = match ($competition->examStatus()) {
        \App\Models\Competition::STATUS_NOT_STARTED => 'bg-gray-100 text-gray-600',
        \App\Models\Competition::STATUS_ENDED       => 'bg-gray-200 text-gray-500',
        default                                     => 'bg-comp text-white',
    };
@endphp
<span class="inline-block mt-2 text-[11px] font-bold px-2.5 py-1 rounded-full {{ $statusClass }}">{{ $competition->examStatusLabel() }}</span>
