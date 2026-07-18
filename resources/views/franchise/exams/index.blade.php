@extends('layouts.franchise')
@section('title', 'Exams')
@section('page-title', 'Exams')

@section('content')

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-fran">All Exams</h2>
        <span class="text-xs text-gray-400">{{ $exams->total() }} total</span>
    </div>

    <div class="divide-y divide-border">
        @forelse($exams as $exam)
            <div class="px-5 py-4 hover:bg-bg-light flex items-center gap-4">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm">{{ $exam->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Level {{ $exam->level?->number }}
                        · {{ $exam->total_questions }}Q
                        · {{ $exam->duration_minutes }}min
                        · Pass {{ number_format($exam->pass_percentage, 0) }}%
                        @if($exam->max_attempts) · Max {{ $exam->max_attempts }} attempts @endif
                    </p>
                    @if($exam->scheduled_at)
                        <p class="text-xs text-fran mt-0.5">{{ $exam->scheduled_at_ist->format('d M Y, H:i') }}</p>
                    @endif
                </div>

                <div>
                    @if($exam->is_active)
                        <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full">Active</span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">Inactive</span>
                    @endif
                </div>

                <div class="text-xs text-gray-400 min-w-[80px] text-right">
                    {{ $exam->created_at?->format('d M Y') ?? '—' }}
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('franchise.exams.show', $exam) }}"
                       class="text-xs bg-fran text-white px-3 py-1.5 rounded-lg font-medium hover:bg-fran-dark">
                        View
                    </a>
                </div>
            </div>
        @empty
            <div class="px-5 py-16 text-center text-gray-400">
                <p class="text-base mb-1">No exams yet</p>
                <p class="text-sm">Exams scheduled by the head office will appear here.</p>
            </div>
        @endforelse
    </div>

    @if($exams->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $exams->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
