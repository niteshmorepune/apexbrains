@props(['status'])

@php
$classes = match($status) {
    'active', 'approved', 'paid', 'confirmed', 'completed', 'passed' => 'bg-green-100 text-green-700',
    'pending', 'registered', 'in_progress' => 'bg-yellow-100 text-yellow-700',
    'suspended', 'rejected', 'overdue', 'failed', 'disqualified' => 'bg-red-100 text-red-700',
    'partial' => 'bg-orange-100 text-orange-700',
    'draft', 'paused' => 'bg-gray-100 text-gray-600',
    default => 'bg-gray-100 text-gray-600',
};
@endphp

<span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $classes }}">
    {{ str_replace('_', ' ', $status) }}
</span>
