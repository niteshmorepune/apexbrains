@extends('layouts.student')
@section('title', 'Notifications')

@section('content')
<div class="p-4 space-y-4">

    @php
        $iconFor = function ($type) {
            return match(true) {
                str_contains($type ?? '', 'exam')     => ['📝', 'bg-fran/10'],
                str_contains($type ?? '', 'result')   => ['📊', 'bg-fran/10'],
                str_contains($type ?? '', 'cert')     => ['🎓', 'bg-stu/10'],
                str_contains($type ?? '', 'fee')      => ['💰', 'bg-yellow-50'],
                str_contains($type ?? '', 'comp')     => ['🏆', 'bg-yellow-50'],
                str_contains($type ?? '', 'practice') => ['🎯', 'bg-stu/10'],
                default                               => ['🔔', 'bg-bg-mid'],
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
            <div class="space-y-2">
                @foreach($items as $n)
                    @php [$emoji, $bg] = $iconFor($n->type); @endphp
                    <div class="bg-white rounded-2xl border {{ $n->is_read ? 'border-border' : 'border-stu/40' }} p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl {{ $bg }} flex items-center justify-center flex-shrink-0">
                                <span class="text-base">{{ $emoji }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-800">{{ $n->title }}</p>
                                    @unless($n->is_read)
                                        <span class="w-2 h-2 rounded-full bg-stu flex-shrink-0"></span>
                                    @endunless
                                </div>
                                <p class="text-sm text-gray-600 mt-0.5 line-clamp-2">{{ $n->message }}</p>
                                <p class="text-xs text-gray-400 mt-1.5">{{ $n->created_at->diffForHumans() }}</p>
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
            <p class="text-xs mt-1">Messages from your franchise will appear here.</p>
        </div>
    @endforelse

    @if($notifications->hasPages())
        <div class="py-3">{{ $notifications->links('pagination::tailwind') }}</div>
    @endif

</div>
@endsection
