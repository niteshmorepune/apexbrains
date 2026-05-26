@extends('layouts.admin')
@section('title', 'Question Details')
@section('page-title', 'Question Details')

@section('page-actions')
    <a href="{{ route('admin.questions.edit', $question) }}"
       class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">Edit</a>
    <a href="{{ route('admin.questions.index') }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">← Bank</a>
@endsection

@section('content')

<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-border p-6 mb-4">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-center gap-2">
                @if($question->level)
                    <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full font-medium">Level {{ $question->level->number }}</span>
                @endif
                @if($question->type === 'audio')
                    <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Audio</span>
                @else
                    <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full">MCQ</span>
                @endif
                <span class="text-xs text-gray-400 capitalize">{{ $question->difficulty }}</span>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                {{ $question->status === 'approved' ? 'bg-stu-light text-stu-dark' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($question->status) }}
            </span>
        </div>

        <p class="text-base text-gray-800 mb-6">{{ $question->question_text }}</p>

        @if($question->option_a)
            <div class="space-y-2">
                @foreach(['A' => $question->option_a, 'B' => $question->option_b, 'C' => $question->option_c, 'D' => $question->option_d] as $label => $option)
                    @if($option)
                        <div class="flex items-center gap-3 p-3 rounded-xl border
                            {{ $question->correct_answer === $label ? 'border-stu bg-stu-light' : 'border-border' }}">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                                {{ $question->correct_answer === $label ? 'bg-stu text-white' : 'bg-bg-mid text-gray-600' }}">{{ $label }}</span>
                            <span class="text-sm text-gray-700">{{ $option }}</span>
                            @if($question->correct_answer === $label)
                                <span class="ml-auto text-xs text-stu font-medium">Correct</span>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-border p-5">
        <h3 class="text-sm font-bold text-admin mb-3">Metadata</h3>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-xs text-gray-500">Category</dt><dd>{{ $question->question_category ?? '—' }}</dd></div>
            <div><dt class="text-xs text-gray-500">Created</dt><dd>{{ $question->created_at->format('d M Y') }}</dd></div>
            @if($question->approved_at)
                <div><dt class="text-xs text-gray-500">Approved by</dt><dd>{{ $question->approvedBy?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Approved at</dt><dd>{{ $question->approved_at->format('d M Y') }}</dd></div>
            @endif
            @if($question->source_pdf)
                <div class="col-span-2"><dt class="text-xs text-gray-500">Source PDF</dt><dd class="text-xs font-mono">{{ $question->source_pdf }}</dd></div>
            @endif
        </dl>
    </div>
</div>

@endsection
