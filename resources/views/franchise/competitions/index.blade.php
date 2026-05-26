@extends('layouts.franchise')
@section('title', 'Competition Registrations')
@section('page-title', 'Competition Registrations')

@section('content')

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

@forelse($competitions as $competition)
    <div class="bg-white rounded-2xl border border-border overflow-hidden mb-5" x-data="{ open: false }">

        {{-- Competition header --}}
        <div class="px-5 py-4 flex items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-sm font-bold text-gray-800">{{ $competition->title }}</h2>
                    @if($competition->is_active)
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                    @endif
                    @if($competition->is_open_to_external)
                        <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">Open to External</span>
                    @endif
                </div>
                <div class="flex items-center gap-4 mt-1.5 text-xs text-gray-500 flex-wrap">
                    <span>{{ ucfirst($competition->competition_type) }}</span>
                    @if($competition->start_date)
                        <span>{{ $competition->start_date->format('d M Y') }} – {{ $competition->end_date?->format('d M Y') }}</span>
                    @endif
                    @if($competition->registration_deadline)
                        <span>Deadline: <span class="text-fran font-medium">{{ $competition->registration_deadline->format('d M Y') }}</span></span>
                    @endif
                    @if($competition->fee_amount > 0)
                        <span>Fee: ₹{{ number_format($competition->fee_amount, 0) }}</span>
                    @endif
                </div>
                @if($competition->description)
                    <p class="text-xs text-gray-400 mt-1">{{ $competition->description }}</p>
                @endif
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-2xl font-black text-gray-800">{{ $competition->registrations->count() }}</p>
                <p class="text-xs text-gray-400">
                    @if($competition->max_participants)
                        / {{ $competition->max_participants }} registered
                    @else
                        registered
                    @endif
                </p>
            </div>
        </div>

        {{-- Register student form --}}
        @if($competition->is_active)
            <div class="px-5 pb-4">
                <button @click="open = !open"
                        class="text-xs text-fran font-medium hover:underline flex items-center gap-1">
                    <span x-text="open ? '▲ Hide form' : '▼ Register a student'"></span>
                </button>
                <div x-show="open" x-cloak class="mt-3">
                    @if($errors->any() && old('_competition_id') == $competition->id)
                        <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('franchise.competitions.register', $competition) }}"
                          class="flex items-center gap-3">
                        @csrf
                        <input type="hidden" name="_competition_id" value="{{ $competition->id }}">
                        <select name="student_id" required
                                class="flex-1 border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select student…</option>
                            @foreach($students as $student)
                                @php
                                    $alreadyIn = $competition->registrations->contains('student_id', $student->id);
                                @endphp
                                <option value="{{ $student->id }}" @disabled($alreadyIn)>
                                    {{ $student->full_name }}
                                    @if($alreadyIn) (registered) @endif
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark flex-shrink-0">
                            Register
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Registered students list --}}
        @if($competition->registrations->isNotEmpty())
            <div class="border-t border-border divide-y divide-border">
                @foreach($competition->registrations as $reg)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full bg-fran/10 text-fran flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold">{{ strtoupper(substr($reg->student?->first_name ?? '?', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $reg->student?->full_name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ ucfirst($reg->student_type) }}
                                · Registered {{ $reg->registration_date?->format('d M Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($reg->payment_status === 'paid')
                                <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Paid</span>
                            @elseif($reg->payment_status === 'pending')
                                <span class="text-xs bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full">Payment Pending</span>
                            @endif
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ ucfirst($reg->status) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="border-t border-border px-5 py-6 text-center text-xs text-gray-400">
                No students registered yet.
            </div>
        @endif
    </div>
@empty
    <div class="bg-white rounded-2xl border border-border px-5 py-16 text-center text-gray-400">
        <p class="text-base mb-1">No competitions yet</p>
        <p class="text-sm">Competitions are created by the admin and assigned to your franchise.</p>
    </div>
@endforelse

@endsection
