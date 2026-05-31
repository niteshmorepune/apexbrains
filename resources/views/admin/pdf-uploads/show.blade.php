@extends('layouts.admin')
@section('title', 'PDF Upload Details')
@section('page-title', 'PDF Upload Details')

@section('page-actions')
    <a href="{{ route('admin.pdf-uploads.index') }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
        ← All Uploads
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="col-span-2">
        {{-- Extracted questions --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                <h2 class="text-sm font-semibold text-admin">
                    Extracted Questions ({{ $extractedQuestions->count() }})
                </h2>
                @if($extractedQuestions->where('status', 'pending')->count() > 0)
                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">
                        {{ $extractedQuestions->where('status', 'pending')->count() }} pending review
                    </span>
                @endif
            </div>
            <div class="divide-y divide-border">
                @forelse($extractedQuestions as $q)
                    <div class="px-5 py-4 hover:bg-bg-light">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-sm text-gray-800 mb-1">{{ $q->question_text }}</p>
                                @if($q->option_a)
                                    <div class="grid grid-cols-2 gap-1 text-xs text-gray-500 mt-2">
                                        <span>A. {{ $q->option_a }}</span>
                                        <span>B. {{ $q->option_b }}</span>
                                        <span>C. {{ $q->option_c }}</span>
                                        <span>D. {{ $q->option_d }}</span>
                                    </div>
                                    @if($q->correct_answer)
                                        <p class="text-xs text-stu mt-1">Correct: {{ $q->correct_answer }}</p>
                                    @endif
                                @endif
                                <div class="flex items-center gap-2 mt-2">
                                    @if($q->level)
                                        <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full">L{{ $q->level->number }}</span>
                                    @endif
                                    <span class="text-xs capitalize text-gray-400">{{ $q->difficulty }}</span>
                                </div>
                            </div>
                            @if($q->status === 'pending')
                                <div class="flex gap-2 flex-shrink-0">
                                    <form method="POST" action="{{ route('admin.questions.approve', $q) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 bg-stu text-white rounded-lg text-xs font-medium">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.questions.reject', $q) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 border border-red-300 text-red-600 rounded-lg text-xs font-medium">Reject</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $q->status === 'approved' ? 'bg-stu-light text-stu-dark' : 'bg-red-50 text-red-600' }}">
                                    {{ ucfirst($q->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-gray-400">
                        No questions extracted yet. Processing may still be in progress.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- PDF info sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-admin mb-4">Upload Details</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-500 mb-0.5">Filename</dt>
                    <dd class="font-medium text-gray-800 break-all">{{ $pdfUpload->original_filename }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 mb-0.5">Status</dt>
                    <dd>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $pdfUpload->status === 'processed' ? 'bg-stu-light text-stu-dark' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($pdfUpload->status) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 mb-0.5">Questions Extracted</dt>
                    <dd class="font-bold text-admin">{{ $pdfUpload->questions_extracted }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 mb-0.5">Uploaded By</dt>
                    <dd class="text-gray-600">{{ $pdfUpload->uploadedBy?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 mb-0.5">Upload Date</dt>
                    <dd class="text-gray-600">{{ $pdfUpload->created_at->format('d M Y, H:i') }}</dd>
                </div>
                @if($pdfUpload->processed_at)
                    <div>
                        <dt class="text-xs text-gray-500 mb-0.5">Processed At</dt>
                        <dd class="text-gray-600">{{ $pdfUpload->processed_at->format('d M Y, H:i') }}</dd>
                    </div>
                @endif
                @if($pdfUpload->error_message)
                    <div>
                        <dt class="text-xs text-gray-500 mb-0.5">Error</dt>
                        <dd class="text-red-500 text-xs">{{ $pdfUpload->error_message }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
</div>

@endsection
