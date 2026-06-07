@props(['title' => '', 'back' => null, 'subtitle' => null])

{{-- Per-screen top header: back chevron + centered title (matches Figma student screens). --}}
<div class="px-4 pt-5 pb-3 flex items-center gap-2">
    @if($back)
        <a href="{{ $back }}" class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700 flex-shrink-0">
    @else
        <button type="button" onclick="if(history.length>1){history.back()}else{location.href='{{ route('student.home') }}'}"
                class="w-8 h-8 -ml-1 flex items-center justify-center text-gray-700 flex-shrink-0">
    @endif
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
    @if($back)</a>@else</button>@endif

    <div class="flex-1 text-center pr-7 min-w-0">
        <h1 class="text-[17px] font-bold text-gray-900 truncate">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-xs text-gray-400 truncate">{{ $subtitle }}</p>
        @endif
    </div>
</div>
