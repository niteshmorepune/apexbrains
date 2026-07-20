@props(['level', 'size' => 'sm'])

@php
$colors = [
    1  => '#87CEEB', 2  => '#2ECC71', 3  => '#00BCD4', 4  => '#FFD54F',
    5  => '#F5A623', 6  => '#FF69B4', 7  => '#D42B2B', 8  => '#9C27B0',
    9  => '#1A73E8', 10 => '#00897B', 11 => '#FF6F00', 12 => '#AD1457',
    13 => '#283593', 14 => '#212121',
];
$num = is_object($level) ? $level->number : (int) $level;
$color = $colors[$num] ?? '#888888';
$sizeClass = $size === 'sm' ? 'text-xs px-2 py-0.5' : 'text-sm px-3 py-1';
@endphp

<span class="{{ $sizeClass }} rounded-full font-semibold inline-flex items-center gap-1"
      style="background-color: {{ $color }}20; color: {{ $color }}; border: 1px solid {{ $color }}40">
    {{ is_object($level) && isset($level->title) ? $level->title : 'L' . $num }}
</span>
