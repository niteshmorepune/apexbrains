@extends('layouts.admin')
@section('title', 'PDF Upload & AI Extraction')
@section('page-title', 'PDF Upload & AI Extraction')

@section('page-actions')
    <a href="{{ route('admin.questions.index') }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
        ← Question Bank
    </a>
@endsection

@section('content')

{{-- KPI Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-admin">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-500 mt-1">PDFs Uploaded</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-fran">{{ number_format($stats['extracted']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Questions Extracted</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-logo-amber">{{ $stats['pending'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Pending Review</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-stu">{{ $stats['bank_ready'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Bank-Ready</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Upload + pipeline --}}
    <div class="col-span-2 space-y-4">

        {{-- Upload area --}}
        <div class="bg-white rounded-2xl border border-border p-6">
            <h2 class="text-sm font-bold text-admin mb-4">Upload New PDF</h2>
            <form method="POST" action="{{ route('admin.pdf-uploads.store') }}" enctype="multipart/form-data">
                @csrf
                <label for="pdf_file"
                       class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-border rounded-xl cursor-pointer hover:border-fran hover:bg-blue-50 transition-colors mb-4">
                    <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm text-gray-500">Drag & drop PDF here, or <span class="text-fran font-medium">browse</span></p>
                    <p class="text-xs text-gray-400 mt-1">Max file size: 20 MB</p>
                    <input id="pdf_file" name="pdf_file" type="file" accept=".pdf" class="hidden">
                </label>
                @error('pdf_file')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror
                <button type="submit"
                        class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Upload &amp; Begin Extraction
                </button>
            </form>
        </div>

        {{-- Pipeline status (static visual for pending uploads) --}}
        @if($uploads->where('status', 'pending')->count() > 0 || $uploads->where('status', 'processing')->count() > 0)
            <div class="bg-white rounded-2xl border border-border p-6">
                <h2 class="text-sm font-bold text-admin mb-4">Processing Pipeline</h2>
                <div class="flex items-center gap-2">
                    @foreach(['PDF Received' => 'done', 'OCR Running' => 'active', 'NLP Identification' => 'waiting', 'MCQ Tagging' => 'waiting'] as $step => $state)
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-3 rounded-full flex-shrink-0
                                    {{ $state === 'done' ? 'bg-stu' : ($state === 'active' ? 'bg-logo-amber animate-pulse' : 'bg-gray-200') }}"></div>
                                <span class="text-xs font-medium {{ $state === 'done' ? 'text-stu' : ($state === 'active' ? 'text-logo-amber' : 'text-gray-400') }}">{{ $step }}</span>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div class="w-6 h-px bg-border flex-shrink-0 mb-4"></div>
                        @endif
                    @endforeach
                </div>
                {{-- Simulated progress --}}
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Extracting questions...</span>
                        <span>64%</span>
                    </div>
                    <div class="h-2 bg-bg-mid rounded-full">
                        <div class="h-2 bg-logo-amber rounded-full transition-all" style="width: 64%"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Uploads table --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border">
                <h2 class="text-sm font-semibold text-admin">Upload History</h2>
            </div>
            <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="bg-admin">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-white">Filename</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Extracted</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Uploaded</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($uploads as $upload)
                        <tr class="hover:bg-bg-light">
                            <td class="px-5 py-3">
                                <p class="font-medium text-admin text-sm">{{ $upload->original_filename }}</p>
                                @if($upload->uploadedBy)
                                    <p class="text-xs text-gray-400">by {{ $upload->uploadedBy->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-medium">
                                {{ $upload->questions_extracted ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($upload->status === 'processed')
                                    <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full font-medium">Processed</span>
                                @elseif($upload->status === 'processing')
                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Processing</span>
                                @elseif($upload->status === 'failed')
                                    <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Failed</span>
                                @else
                                    <span class="text-xs bg-bg-mid text-gray-500 px-2 py-0.5 rounded-full">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 text-xs">
                                {{ $upload->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.pdf-uploads.show', $upload) }}"
                                   class="text-xs text-fran hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                                No PDFs uploaded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table></div>
            @if($uploads->hasPages())
                <div class="px-5 py-4 border-t border-border">
                    {{ $uploads->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>


</div>

{{-- Extracted Questions — Review and Approve --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-border flex items-center justify-between">
        <h2 class="text-sm font-semibold text-admin">Extracted Questions — Review and Approve</h2>
        <span class="text-xs text-gray-400">{{ $reviewQuestions->total() }} questions</span>
    </div>

    {{-- Review tabs --}}
    <div class="px-5 pt-4 flex gap-1 border-b border-border">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $tab => $label)
            <a href="{{ route('admin.pdf-uploads.index', array_merge(request()->except('rpage'), ['review_tab' => $tab])) }}"
               class="px-4 py-2 rounded-t-xl text-sm font-medium transition-colors border-b-2 -mb-px
                      {{ $reviewTab === $tab ? 'border-fran text-fran' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
                @if($tab === 'pending' && $stats['pending'] > 0)
                    <span class="ml-1 bg-logo-amber text-white text-xs px-1.5 py-0.5 rounded-full">{{ $stats['pending'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    @forelse($reviewQuestions as $q)
        <div class="px-5 py-4 border-b border-border last:border-b-0 hover:bg-bg-light flex items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono text-gray-400">Q-{{ str_pad($q->id, 4, '0', STR_PAD_LEFT) }}</span>
                    @if($q->level)
                        <span class="text-xs bg-fran-light text-fran px-1.5 py-0.5 rounded-full font-medium">L{{ $q->level->number }}</span>
                    @endif
                    <span class="text-xs text-gray-400 capitalize">{{ $q->question_type }}</span>
                </div>
                <p class="text-sm text-gray-800">{{ $q->question_text }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                @if($q->status !== 'approved')
                    <form method="POST" action="{{ route('admin.questions.approve', $q) }}">
                        @csrf
                        <button type="submit" class="text-xs bg-stu text-white px-3 py-1.5 rounded-lg hover:bg-stu-dark transition-colors font-medium">
                            Approve
                        </button>
                    </form>
                @endif
                @if($q->status !== 'rejected')
                    <form method="POST" action="{{ route('admin.questions.reject', $q) }}">
                        @csrf
                        <button type="submit" class="text-xs border border-red-200 text-red-500 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                            Reject
                        </button>
                    </form>
                @endif
                @if($q->status === 'approved')
                    <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full font-medium">✓ Approved</span>
                @elseif($q->status === 'rejected')
                    <span class="text-xs bg-red-50 text-red-500 px-2 py-0.5 rounded-full">✗ Rejected</span>
                @endif
            </div>
        </div>
    @empty
        <div class="px-5 py-10 text-center text-gray-400 text-sm">
            No extracted questions to review.
        </div>
    @endforelse

    @if($reviewQuestions->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $reviewQuestions->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
