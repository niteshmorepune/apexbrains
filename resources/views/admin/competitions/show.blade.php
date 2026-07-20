@extends('layouts.admin')
@section('title', $competition->title)
@section('page-title', $competition->title)

@section('page-actions')
    <a href="{{ route('admin.competitions.edit', $competition) }}"
       class="px-4 py-2 border border-border text-gray-600 text-sm font-semibold rounded-xl hover:bg-bg-light transition-colors">
        Edit
    </a>
@endsection

@section('content')

@if(session('success'))
    <div class="bg-stu-light border border-green-200 text-stu-dark text-sm rounded-xl px-4 py-3 mb-4">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-4">
        {{ session('error') }}
    </div>
@endif

@if($competition->questionPapers->isEmpty())
    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl px-4 py-3 mb-4">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
        </svg>
        <div>
            <p class="font-semibold">No question papers uploaded yet</p>
            <p class="text-amber-700">Students cannot take this competition until a level-wise paper is uploaded. Use “Upload Paper” below.</p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Registrations</p>
        <p class="text-2xl font-bold text-fran">{{ $competition->registrations_count }}</p>
        @if($competition->max_participants)
            <p class="text-xs text-gray-400 mt-1">of {{ $competition->max_participants }} max</p>
        @endif
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Submitted Attempts</p>
        <p class="text-2xl font-bold text-admin">{{ $attempts->count() }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Entry Fee</p>
        <p class="text-2xl font-bold text-logo-amber">₹{{ number_format($competition->fee_amount) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-5">
        <p class="text-xs text-gray-500 mb-1">Status</p>
        <p class="text-2xl font-bold {{ $competition->is_active ? 'text-stu' : 'text-gray-400' }}">
            {{ $competition->is_active ? 'Active' : 'Inactive' }}
        </p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-border p-6">
    <h2 class="text-sm font-semibold text-admin mb-4">Details</h2>
    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-gray-500 mb-0.5">Type</dt><dd class="capitalize font-medium">{{ $competition->competition_type }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Open to External</dt><dd>{{ $competition->is_open_to_external ? 'Yes' : 'No' }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Registration Deadline</dt><dd>{{ $competition->registration_deadline->format('d M Y') }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">Start Date</dt><dd>{{ $competition->start_date->format('d M Y') }}</dd></div>
        <div><dt class="text-gray-500 mb-0.5">End Date</dt><dd>{{ $competition->end_date->format('d M Y') }}</dd></div>
        @if($competition->description)
            <div class="col-span-2"><dt class="text-gray-500 mb-0.5">Description</dt><dd>{{ $competition->description }}</dd></div>
        @endif
    </dl>
</div>

{{-- Competition question papers (level-wise, CSV-uploaded, deletable) --}}
<div class="bg-white rounded-2xl border border-border p-6 mt-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-admin">Question Papers</h2>
        <a href="{{ route('admin.competitions.papers.create', $competition) }}"
           class="inline-flex items-center gap-2 bg-fran text-white text-xs font-semibold px-3 py-2 rounded-xl hover:bg-fran-dark transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Upload Paper
        </a>
    </div>

    @if($competition->questionPapers->isEmpty())
        <p class="text-sm text-gray-400 py-6 text-center">
            No question papers uploaded yet. Upload level-wise papers via CSV when the competition is scheduled.
        </p>
    @else
        <div class="overflow-x-auto"><table class="w-full min-w-[560px] text-sm">
            <thead>
                <tr class="border-b border-border text-left text-xs text-gray-500">
                    <th class="py-2 pr-4 font-semibold">Title</th>
                    <th class="py-2 px-4 font-semibold text-center">Level</th>
                    <th class="py-2 px-4 font-semibold text-center">Questions</th>
                    <th class="py-2 px-4 font-semibold text-center">Duration</th>
                    <th class="py-2 px-4 font-semibold text-center">Pass %</th>
                    <th class="py-2 pl-4 font-semibold text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach($competition->questionPapers as $paper)
                    <tr>
                        <td class="py-3 pr-4 font-medium text-admin">{{ $paper->title }}</td>
                        <td class="py-3 px-4 text-center text-gray-600">{{ $paper->level?->title ?? '—' }}</td>
                        <td class="py-3 px-4 text-center font-medium text-gray-700">{{ $paper->items_count }}</td>
                        <td class="py-3 px-4 text-center text-gray-600">{{ $paper->duration_minutes }} min</td>
                        <td class="py-3 px-4 text-center text-gray-600">{{ $paper->pass_percentage }}%</td>
                        <td class="py-3 pl-4 text-center">
                            <form method="POST" action="{{ route('admin.competitions.papers.destroy', [$competition, $paper]) }}"
                                  onsubmit="return confirm('Delete paper “{{ $paper->title }}” and all its questions?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    @endif
</div>

{{-- Results declaration --}}
<div class="bg-white rounded-2xl border border-border p-6 mt-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold text-admin">Results</h2>
        @if($competition->results_declared_at)
            <span class="text-xs font-medium text-stu bg-stu-light px-3 py-1.5 rounded-full">
                Declared {{ $competition->results_declared_at->format('d M Y, g:i A') }}
            </span>
        @elseif($attempts->isNotEmpty())
            <form method="POST" action="{{ route('admin.competitions.declare-results', $competition) }}"
                  onsubmit="return confirm('Declare results for {{ $attempts->count() }} submitted attempt(s)? Students will immediately see their score, rank, and certificate. This cannot be undone.');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 bg-stu text-white text-xs font-semibold px-3 py-2 rounded-xl hover:bg-stu-dark transition-colors">
                    Declare Results
                </button>
            </form>
        @endif
    </div>

    @if($attempts->isEmpty())
        <p class="text-sm text-gray-400 py-6 text-center">No submitted attempts yet.</p>
    @else
        @unless($competition->results_declared_at)
            <p class="text-xs text-gray-500 mb-3">Students see only a "submitted successfully" message until you declare results.</p>
        @endunless
        <div class="overflow-x-auto"><table class="w-full min-w-[480px] text-sm">
            <thead>
                <tr class="border-b border-border text-left text-xs text-gray-500">
                    <th class="py-2 pr-4 font-semibold">Rank</th>
                    <th class="py-2 px-4 font-semibold">Student</th>
                    <th class="py-2 px-4 font-semibold text-center">Score</th>
                    <th class="py-2 pl-4 font-semibold text-center">Submitted</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach($attempts as $i => $attempt)
                    <tr>
                        <td class="py-3 pr-4 font-semibold text-admin">#{{ $i + 1 }}</td>
                        <td class="py-3 px-4 text-gray-700">{{ $attempt->student?->full_name ?? '—' }}</td>
                        <td class="py-3 px-4 text-center font-medium text-gray-700">{{ number_format($attempt->percentage, 0) }}%</td>
                        <td class="py-3 pl-4 text-center text-gray-500">{{ $attempt->submitted_at?->format('d M Y, g:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    @endif
</div>

@endsection
