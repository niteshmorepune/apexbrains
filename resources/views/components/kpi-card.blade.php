@props(['label', 'value', 'icon' => null, 'color' => 'blue', 'trend' => null, 'trendValue' => null])

<div class="bg-white rounded-xl p-5 border border-border shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $value }}</p>
            @if($trend && $trendValue)
                <p class="text-xs mt-1 {{ $trend === 'up' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $trend === 'up' ? '▲' : '▼' }} {{ $trendValue }}
                </p>
            @endif
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-lg bg-{{ $color }}-50 flex items-center justify-center flex-shrink-0">
                <span class="text-{{ $color }}-600 text-lg">{{ $icon }}</span>
            </div>
        @endif
    </div>
</div>
