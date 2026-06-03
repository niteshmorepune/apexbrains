@props([
    'subtitle' => 'Abacus',
    'subtitleClass' => 'text-gray-400',
    'size' => 'md', // sm | md | lg
])
@php
    $icon = ['sm' => 'w-8 h-8', 'md' => 'w-10 h-10', 'lg' => 'w-11 h-11'][$size] ?? 'w-10 h-10';
    $word = ['sm' => 'text-[17px]', 'md' => 'text-[20px]', 'lg' => 'text-[22px]'][$size] ?? 'text-[20px]';
@endphp
<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    {{-- Abacus mark --}}
    <svg class="{{ $icon }} flex-shrink-0" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect x="6" y="7" width="36" height="34" rx="4" stroke="#1A73E8" stroke-width="2.5"/>
        <line x1="6" y1="18" x2="42" y2="18" stroke="#1A73E8" stroke-width="2"/>
        <line x1="6" y1="30" x2="42" y2="30" stroke="#1A73E8" stroke-width="2"/>
        <circle cx="14" cy="12.5" r="3.5" fill="#EA4335"/>
        <circle cx="24" cy="12.5" r="3.5" fill="#FBBC05"/>
        <circle cx="34" cy="12.5" r="3.5" fill="#34A853"/>
        <circle cx="14" cy="24" r="3.5" fill="#FBBC05"/>
        <circle cx="24" cy="24" r="3.5" fill="#34A853"/>
        <circle cx="34" cy="24" r="3.5" fill="#1A73E8"/>
        <circle cx="14" cy="35.5" r="3.5" fill="#34A853"/>
        <circle cx="24" cy="35.5" r="3.5" fill="#EA4335"/>
        <circle cx="34" cy="35.5" r="3.5" fill="#FBBC05"/>
    </svg>
    <div class="leading-tight">
        <div class="{{ $word }} font-extrabold tracking-tight whitespace-nowrap">
            <span class="text-logo-red">A</span><span class="text-fran">p</span><span class="text-[#34A853]">e</span><span class="text-logo-amber">x</span><span class="text-logo-red"> B</span><span class="text-fran">r</span><span class="text-[#34A853]">a</span><span class="text-logo-amber">i</span><span class="text-logo-red">n</span><span class="text-fran">s</span>
        </div>
        @if($subtitle)
            <div class="text-[10px] font-semibold tracking-[0.26em] uppercase {{ $subtitleClass }}">{{ $subtitle }}</div>
        @endif
    </div>
</div>
