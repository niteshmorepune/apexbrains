@extends('layouts.franchise')
@section('title', 'Fee Reminders')
@section('page-title', 'Fee Reminders — Outstanding Fees')

@section('breadcrumb')
    <a href="{{ route('franchise.fees.index') }}" class="text-white/70 hover:text-white">Fees</a>
    <span class="mx-1 text-white/40">/</span>
    <span>Reminders</span>
@endsection

@section('page-actions')
    <button onclick="document.querySelectorAll('.remind-btn').forEach(b => b.click())"
            class="px-5 py-2 bg-white text-fran rounded-xl text-sm font-semibold hover:bg-blue-50 transition-colors">
        Send All Reminders ({{ $fees->count() }})
    </button>
@endsection

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Due This Month</p>
        <p class="text-2xl font-bold text-logo-amber">₹{{ number_format($stats['due_this_month']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['due_count'] }} students</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Overdue 30d+</p>
        <p class="text-2xl font-bold text-red-500">₹{{ number_format($stats['overdue_30']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['overdue_30_count'] }} students</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Overdue 60d+</p>
        <p class="text-2xl font-bold text-red-700">₹{{ number_format($stats['overdue_60']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['overdue_60_count'] }} students</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Total Outstanding</p>
        <p class="text-2xl font-bold text-admin">₹{{ number_format($stats['total_outstanding']) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $stats['total_count'] }} students</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-fran">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Student</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-white">Parent Contact</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Amount</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Due Date</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-white">Overdue By</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Priority</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Last Reminded</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($fees as $fee)
                @php
                    $priority = $fee->priority;
                    $priorityClass = match($priority) {
                        'critical' => 'bg-red-100 text-red-700',
                        'high'     => 'bg-red-50 text-red-500',
                        'medium'   => 'bg-yellow-100 text-yellow-700',
                        default    => 'bg-gray-100 text-gray-500',
                    };
                    $phone = $fee->student?->primaryParent?->phone ?? $fee->student?->parent_phone;
                    $whatsapp = $fee->student?->primaryParent?->whatsapp ?? $fee->student?->parent_whatsapp;
                @endphp
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $fee->student?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($fee->student?->currentLevel)
                            <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full">{{ $fee->student->currentLevel->title }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm">{{ $phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-admin">₹{{ number_format($fee->amount) }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $fee->due_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($fee->overdue_days > 0)
                            <span class="text-xs font-medium text-red-500">{{ $fee->overdue_days }} days</span>
                        @else
                            <span class="text-xs text-gray-400">Not yet</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $priorityClass }}">
                            {{ $priority }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-400">
                        {{ $fee->last_reminded_at?->format('d M') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            @if($whatsapp)
                                <a href="https://wa.me/91{{ preg_replace('/\D/', '', $whatsapp) }}?text={{ urlencode('Dear parent, the fee of ₹' . number_format($fee->amount) . ' for ' . $fee->student?->full_name . ' is due/overdue. Please pay at the earliest.') }}"
                                   target="_blank"
                                   class="remind-btn text-xs bg-stu text-white px-2 py-1 rounded-lg hover:bg-stu-dark transition-colors">
                                    WhatsApp
                                </a>
                            @endif
                            @if($phone)
                                <a href="{{ route('franchise.fees.reminder', $fee) }}"
                                   class="text-xs border border-fran text-fran px-2 py-1 rounded-lg hover:bg-fran-light transition-colors">
                                    SMS
                                </a>
                                <a href="tel:{{ $phone }}"
                                   class="text-xs border border-border text-gray-500 px-2 py-1 rounded-lg hover:bg-bg-light transition-colors">
                                    Call
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                        <div class="text-3xl mb-2">✅</div>
                        <p class="font-medium text-gray-500">No outstanding fees!</p>
                        <p class="text-sm mt-1">All students are up to date with payments.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@endsection
