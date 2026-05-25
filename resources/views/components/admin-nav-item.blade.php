@props(['route', 'icon', 'label'])

@php $isActive = request()->routeIs($route) || request()->routeIs($route . '.*'); @endphp

<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
          {{ $isActive ? 'bg-admin-light text-white' : 'text-gray-400 hover:text-white hover:bg-admin-mid' }}">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        @include("components.icons.{$icon}")
    </svg>
    {{ $label }}
</a>
