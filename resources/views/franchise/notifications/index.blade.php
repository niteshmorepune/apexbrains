@extends('layouts.franchise')
@section('title', 'Notification Center')
@section('page-title', 'Notification Center')

@section('content')

<div class="grid grid-cols-3 gap-6">

    {{-- Compose --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-6" x-data="{ target: '{{ old('target', 'all') }}' }">
            <h2 class="text-sm font-bold text-fran mb-4">Send Notification</h2>

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('franchise.notifications.send') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required maxlength="150"
                               placeholder="e.g. Fee Reminder — June"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Message <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="4" required maxlength="500"
                                  placeholder="Type your message here..."
                                  class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('message') }}</textarea>
                        <p class="text-xs text-gray-400 text-right mt-0.5">Max 500 characters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Channel</label>
                        <select name="channel" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="app" @selected(old('channel') === 'app')>In-App</option>
                            <option value="whatsapp" @selected(old('channel') === 'whatsapp')>WhatsApp</option>
                            <option value="sms" @selected(old('channel') === 'sms')>SMS</option>
                            <option value="email" @selected(old('channel') === 'email')>Email</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Send To</label>
                        <select name="target" x-model="target" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="all">All Active Students</option>
                            <option value="level">Specific Level</option>
                            <option value="student">Specific Student</option>
                        </select>
                    </div>
                    <div x-show="target === 'level'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Level</label>
                        <select name="level_id" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Level</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>Level {{ $level->number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="target === 'student'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Student</label>
                        <select name="student_id" class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Student</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                        Send Notification
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- History --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-fran">Notification History</h2>
        </div>
        <div class="divide-y divide-border">
            @forelse($history as $notif)
                <div class="px-5 py-4 hover:bg-bg-light">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $notif->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $notif->message }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $notif->created_at->format('d M Y, H:i') }}
                                · {{ ucfirst($notif->channel) }}
                                @if($notif->student)
                                    · to {{ $notif->student->full_name }}
                                @endif
                            </p>
                        </div>
                        @if($notif->is_read)
                            <span class="text-xs text-green-600 flex-shrink-0">Read</span>
                        @else
                            <span class="text-xs text-gray-400 flex-shrink-0">Sent</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-gray-400">
                    No notifications sent yet. Use the form to send your first message.
                </div>
            @endforelse
        </div>
        @if($history->hasPages())
            <div class="px-5 py-4 border-t border-border">
                {{ $history->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>

@endsection
