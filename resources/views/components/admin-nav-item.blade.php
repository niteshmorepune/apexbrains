@props(['route', 'icon', 'label'])

@php $active = request()->routeIs($route) || request()->routeIs($route . '.*'); @endphp

<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors relative
          {{ $active ? 'bg-admin-mid text-white' : 'text-gray-400 hover:text-white hover:bg-admin-mid' }}">
    @if($active)
        <span class="absolute left-0 top-1 bottom-1 w-0.5 bg-logo-amber rounded-r"></span>
    @endif
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        @include("components.icons.{$icon}")
    </svg>
    {{ $label }}
</a>
