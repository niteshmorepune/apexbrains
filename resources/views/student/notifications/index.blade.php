@extends('layouts.student')
@section('title', 'Notifications')

@section('content')
<div class="p-4 space-y-3">

    @forelse($notifications as $n)
        <div class="bg-white rounded-2xl border {{ $n->is_read ? 'border-border' : 'border-fran/40' }} p-4">
            <div class="flex items-start gap-3">
                @unless($n->is_read)
                    <div class="w-2 h-2 rounded-full bg-fran mt-1.5 flex-shrink-0"></div>
                @endunless
                <div class="flex-1 min-w-0 {{ $n->is_read ? '' : '' }}">
                    <p class="text-sm font-semibold text-gray-800">{{ $n->title }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $n->message }}</p>
                    <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-2">
                        <span>{{ $n->created_at->format('d M Y, H:i') }}</span>
                        <span class="text-gray-300">·</span>
                        <span>{{ ucfirst($n->channel) }}</span>
                    </p>
                </div>
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
        <div class="py-3">
            {{ $notifications->links('pagination::tailwind') }}
        </div>
    @endif

</div>
@endsection
