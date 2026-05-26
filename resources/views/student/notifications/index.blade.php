@extends('layouts.student')
@section('title', 'Notifications')

@section('content')
<div class="p-4 space-y-3">

    @forelse($notifications as $n)
        <div class="bg-white rounded-2xl border border-border p-4">
            <p class="text-sm text-gray-800">{{ $n->entity_type }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->format('d M Y, H:i') }}</p>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-border p-12 text-center text-gray-400">
            <p class="text-sm">No notifications yet.</p>
        </div>
    @endforelse

    @if($notifications->hasPages())
        <div class="py-3">
            {{ $notifications->links('pagination::tailwind') }}
        </div>
    @endif

</div>
@endsection
