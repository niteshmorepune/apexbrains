@if(isset($breadcrumbs) && count($breadcrumbs))
<nav class="flex items-center gap-1 text-sm text-gray-500">
    @foreach($breadcrumbs as $i => $crumb)
        @if($i < count($breadcrumbs) - 1)
            <a href="{{ $crumb['url'] }}" class="hover:text-gray-700">{{ $crumb['label'] }}</a>
            <span>/</span>
        @else
            <span class="text-gray-900 font-medium">{{ $crumb['label'] }}</span>
        @endif
    @endforeach
</nav>
@else
<span class="text-sm font-medium text-gray-900">@yield('title', 'Dashboard')</span>
@endif
