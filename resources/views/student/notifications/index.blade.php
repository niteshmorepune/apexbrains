@extends('layouts.student')
@section('title', 'Notifications')

@section('content')
<x-student-header title="Notifications" :back="route('student.home')" />

<div class="px-4 pb-4 space-y-4">

    @php
        $iconFor = function ($type) {
            return match(true) {
                str_contains($type ?? '', 'result')   => ['🏅', 'bg-stu-light'],
                str_contains($type ?? '', 'exam')      => ['📅', 'bg-red-50'],
                str_contains($type ?? '', 'cert')      => ['🎓', 'bg-amber-50'],
                str_contains($type ?? '', 'fee')       => ['💳', 'bg-pink-50'],
                str_contains($type ?? '', 'comp')      => ['🏆', 'bg-amber-50'],
                str_contains($type ?? '', 'practice')  => ['📖', 'bg-fran-light'],
                default                                => ['🔔', 'bg-bg-mid'],
            };
        };
        $groups = $notifications->getCollection()->groupBy(function ($n) {
            if ($n->created_at->isToday()) return 'Today';
            if ($n->created_at->gte(now()->subWeek())) return 'Earlier This Week';
            return 'Older';
        });
    @endphp

    @forelse($groups as $label => $items)
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">{{ $label }}</p>
            <div class="space-y-3">
                @foreach($items as $n)
                    @php [$emoji, $bg] = $iconFor($n->type); @endphp
                    <div class="bg-white rounded-2xl border border-border p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $bg }} flex items-center justify-center flex-shrink-0 text-lg">{{ $emoji }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-bold text-gray-800">{{ $n->title }}</p>
                                    <span class="text-[11px] text-gray-400 flex-shrink-0">{{ $n->created_at->diffForHumans(null, true) }} ago</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $n->message }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-border p-12 text-center text-gray-400">
            <p class="text-2xl mb-2">🔔</p>
            <p class="text-sm font-medium">No notifications yet.</p>
            <p class="text-xs mt-1">Messages from your academy will appear here.</p>
        </div>
    @endforelse

    @if($notifications->hasPages())
        <div class="py-2">{{ $notifications->links('pagination::tailwind') }}</div>
    @endif

</div>
@endsection
