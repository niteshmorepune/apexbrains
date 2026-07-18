@props(['type'])

@php
$config = match($type) {
    'zonal'    => ['label' => 'Zonal',    'class' => 'bg-green-100 text-green-700'],
    'regional' => ['label' => 'Regional', 'class' => 'bg-blue-100 text-blue-700'],
    'national' => ['label' => 'National', 'class' => 'bg-purple-100 text-purple-700'],
    default    => ['label' => ucfirst($type), 'class' => 'bg-gray-100 text-gray-600'],
};
@endphp

<span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $config['class'] }}">
    {{ $config['label'] }}
</span>
