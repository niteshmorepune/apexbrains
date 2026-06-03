@props([
    'subtitle' => null,
    'subtitleClass' => 'text-gray-400',
    'size' => 'md', // sm | md | lg
])
@php
    $h   = ['sm' => 'h-8', 'md' => 'h-10', 'lg' => 'h-14'][$size] ?? 'h-10';
    $src = !empty($appSettings['logo_path'] ?? null)
        ? \Illuminate\Support\Facades\Storage::url($appSettings['logo_path'])
        : asset('images/apex-logo.png');
@endphp
<div {{ $attributes->merge(['class' => 'inline-flex flex-col items-start gap-1']) }}>
    <img src="{{ $src }}" alt="{{ $appSettings['app_name'] ?? 'Apex Brains' }}"
         class="{{ $h }} w-auto max-w-full object-contain object-left self-start" />
    @if($subtitle)
        <span class="text-[10px] font-semibold tracking-[0.22em] uppercase {{ $subtitleClass }}">{{ $subtitle }}</span>
    @endif
</div>
