@props(['paginator'])

@if($paginator->hasPages())
<div class="flex items-center justify-between px-4 py-3 sm:px-6">
    <div class="text-sm text-gray-500">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </div>
    <div class="flex gap-1">
        @if($paginator->onFirstPage())
            <span class="px-3 py-1 text-sm text-gray-400 border border-border rounded cursor-not-allowed">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1 text-sm border border-border rounded hover:bg-bg-mid">←</a>
        @endif

        @foreach($paginator->getUrlRange(max(1, $paginator->currentPage()-2), min($paginator->lastPage(), $paginator->currentPage()+2)) as $page => $url)
            @if($page == $paginator->currentPage())
                <span class="px-3 py-1 text-sm bg-fran text-white rounded">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="px-3 py-1 text-sm border border-border rounded hover:bg-bg-mid">{{ $page }}</a>
            @endif
        @endforeach

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1 text-sm border border-border rounded hover:bg-bg-mid">→</a>
        @else
            <span class="px-3 py-1 text-sm text-gray-400 border border-border rounded cursor-not-allowed">→</span>
        @endif
    </div>
</div>
@endif
