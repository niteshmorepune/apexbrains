@props(['route', 'icon', 'label', 'color' => 'stu'])

@php
$isActive = request()->routeIs($route) || request()->routeIs($route . '.*');
$activeColor = "text-{$color}";
@endphp

<a href="{{ route($route) }}"
   class="flex flex-col items-center justify-center gap-0.5 flex-1 py-1 {{ $isActive ? $activeColor . ' font-semibold' : 'text-gray-400' }}">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        @include("components.icons.{$icon}")
    </svg>
    <span class="text-[10px]">{{ $label }}</span>
</a>
