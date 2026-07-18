@extends('layouts.admin')
@section('title', 'Question Detail')
@section('page-title', 'Question Detail')

@section('page-actions')
    <a href="{{ route('admin.regular-questions.edit', $question) }}"
       class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">Edit</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-border p-6 max-w-2xl">
    <p class="text-xs text-gray-400 mb-1">{{ $question->category->name }} → {{ $question->type->name }}</p>
    <h2 class="text-base font-semibold text-admin mb-4">{{ $question->question_text }}</h2>

    @if($question->answer_format === 'mcq')
        <div class="grid grid-cols-2 gap-2 text-sm mb-4">
            @foreach(['a', 'b', 'c', 'd'] as $key)
                @if($question->{'option_' . $key})
                    <div class="px-3 py-2 rounded-xl border {{ $question->correct_answer === $key ? 'border-green-400 bg-green-50 text-green-700' : 'border-border' }}">
                        <span class="font-semibold uppercase">{{ $key }}.</span> {{ $question->{'option_' . $key} }}
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500 mb-4">Audio question — no MCQ options.</p>
    @endif

    <dl class="grid grid-cols-2 gap-3 text-xs text-gray-500 border-t border-border pt-4">
        <div><dt class="font-medium text-gray-400">Status</dt><dd class="text-gray-700 capitalize">{{ $question->status }}</dd></div>
        <div><dt class="font-medium text-gray-400">Approved by</dt><dd class="text-gray-700">{{ $question->approvedBy?->name ?? '—' }}</dd></div>
        <div><dt class="font-medium text-gray-400">Approved at</dt><dd class="text-gray-700">{{ $question->approved_at?->format('d M Y') ?? '—' }}</dd></div>
    </dl>
</div>
@endsection
