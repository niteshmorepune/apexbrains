@extends('layouts.admin')
@section('title', 'Exams')
@section('page-title', 'Exams')

@section('page-actions')
    <a href="{{ route('admin.exams.create') }}"
       class="inline-flex items-center gap-2 bg-fran text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-fran-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Exam
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="bg-stu-light border border-green-200 text-stu-dark text-sm rounded-xl px-4 py-3 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-4">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Title</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Questions</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Duration</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Pass %</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Attempts</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($exams as $exam)
                <tr class="hover:bg-bg-light">
                    <td class="px-5 py-3">
                        <p class="font-medium text-admin">{{ $exam->title }}</p>
                        @if($exam->scheduled_at)
                            <p class="text-xs text-gray-400">{{ $exam->scheduled_at->format('d M Y, H:i') }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">Level {{ $exam->level?->number }}</td>
                    <td class="px-4 py-3 text-center font-medium text-gray-700">{{ $exam->total_questions }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $exam->duration_minutes }} min</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ number_format($exam->pass_percentage, 0) }}%</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $exam->attempts_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($exam->is_active)
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Active</span>
                        @else
                            <span class="text-xs bg-bg-mid text-gray-400 px-2 py-0.5 rounded-full">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.exams.show', $exam) }}" class="text-xs text-fran hover:underline font-medium">View</a>
                            <a href="{{ route('admin.exams.edit', $exam) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                        No exams yet.
                        <a href="{{ route('admin.exams.create') }}" class="text-fran hover:underline ml-1">Create one →</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>

    @if($exams->hasPages())
        <div class="px-5 py-4 border-t border-border">{{ $exams->links('pagination::tailwind') }}</div>
    @endif
</div>

@endsection
