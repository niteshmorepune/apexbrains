@extends('layouts.admin')
@section('title', 'AI Question Bank')
@section('page-title', 'AI Question Bank')

@section('page-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.questions.audio') }}"
           class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
            Audio Generator
        </a>
        <a href="{{ route('admin.questions.import') }}"
           class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
            Bulk Import
        </a>
        <a href="{{ route('admin.questions.create') }}"
           class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            + Add Question
        </a>
    </div>
@endsection

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-admin">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Questions</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-fran">{{ number_format($stats['mcq']) }}</p>
        <p class="text-xs text-gray-500 mt-1">MCQ</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-stu">{{ number_format($stats['audio']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Audio</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-logo-amber">{{ number_format($stats['pending']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Pending Review</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-stu-dark">{{ number_format($stats['approved']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Approved</p>
    </div>
</div>

<div class="grid grid-cols-[240px_1fr] gap-5 items-start">

{{-- Left Filter Panel --}}
<div class="bg-white rounded-2xl border border-border p-5 sticky top-5">
    <form method="GET" action="{{ route('admin.questions.index') }}" id="filterForm">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="mb-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions..."
                   class="w-full border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        </div>

        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">By Level</p>
        <div class="flex flex-wrap gap-1.5 mb-4">
            <label class="cursor-pointer">
                <input type="radio" name="level" value="" class="sr-only peer"
                       {{ !request('level') ? 'checked' : '' }}
                       onchange="document.getElementById('filterForm').submit()">
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium border transition-colors
                             peer-checked:bg-fran peer-checked:text-white peer-checked:border-fran
                             border-border text-gray-600 hover:border-fran">All</span>
            </label>
            @foreach($levels as $level)
                <label class="cursor-pointer">
                    <input type="radio" name="level" value="{{ $level->id }}" class="sr-only peer"
                           {{ request('level') == $level->id ? 'checked' : '' }}
                           onchange="document.getElementById('filterForm').submit()">
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium border transition-colors
                                 peer-checked:bg-fran peer-checked:text-white peer-checked:border-fran
                                 border-border text-gray-600 hover:border-fran">L{{ $level->number }}</span>
                </label>
            @endforeach
        </div>

        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">By Type</p>
        <div class="space-y-1.5 mb-4">
            @foreach(['' => 'Both', 'mcq' => 'MCQ', 'audio' => 'Audio'] as $val => $lbl)
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="type" value="{{ $val }}" class="accent-fran"
                           {{ request('type', '') === $val ? 'checked' : '' }}
                           onchange="document.getElementById('filterForm').submit()">
                    <span class="text-sm text-gray-700">{{ $lbl }}</span>
                </label>
            @endforeach
        </div>

        <button type="submit" class="w-full py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
            Apply Filters
        </button>
        @if(request('search') || request('level') || request('type'))
            <a href="{{ route('admin.questions.index', ['tab' => $tab]) }}"
               class="block text-center text-xs text-gray-400 hover:text-gray-600 mt-2">Clear all</a>
        @endif
    </form>
</div>

{{-- Right: tabs + table --}}
<div>

{{-- Tab filters --}}
<div class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="flex border-b border-border">
        @foreach(['all' => 'All Questions', 'mcq' => 'MCQ', 'audio' => 'Audio', 'pending' => 'Pending Review'] as $key => $label)
            <a href="{{ route('admin.questions.index', array_merge(request()->except('tab', 'page'), ['tab' => $key])) }}"
               class="px-5 py-3 text-sm font-medium border-b-2 transition-colors
                      {{ $tab === $key ? 'border-fran text-fran' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
                @if($key === 'pending' && $stats['pending'] > 0)
                    <span class="ml-1 bg-logo-amber text-white text-xs rounded-full px-1.5 py-0.5">{{ $stats['pending'] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto"><table class="w-full min-w-[640px] text-sm">
        <thead>
            <tr class="bg-admin">
                <th class="text-left px-4 py-3 text-xs font-semibold text-white w-20">ID</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-white">Question</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Type</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($questions as $q)
                <tr class="hover:bg-bg-light {{ $q->status === 'pending' ? 'bg-yellow-50' : '' }}">
                    <td class="px-4 py-3">
                        <span class="text-xs font-mono text-gray-400">Q-{{ str_pad($q->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-gray-800 line-clamp-2 max-w-lg">{{ $q->question_text }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($q->level)
                            <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full font-medium">L{{ $q->level->number }}</span>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($q->type === 'audio')
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Audio</span>
                        @else
                            <span class="text-xs bg-bg-mid text-gray-600 px-2 py-0.5 rounded-full">MCQ</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($q->status === 'approved')
                            <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full">Approved</span>
                        @elseif($q->status === 'pending')
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Pending</span>
                        @else
                            <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full">Rejected</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            @if($q->status === 'pending')
                                <form method="POST" action="{{ route('admin.questions.approve', $q) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-stu hover:underline font-medium">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.questions.reject', $q) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-500 hover:underline font-medium">Reject</button>
                                </form>
                            @else
                                <a href="{{ route('admin.questions.edit', $q) }}" class="text-xs text-fran hover:underline">Edit</a>
                                @if($q->type === 'audio')
                                    <button type="button" onclick="speakQuestion(@js($q->question_text), this)"
                                            class="text-xs text-stu hover:underline">▶ Play</button>
                                @endif
                                <form method="POST" action="{{ route('admin.questions.destroy', $q) }}"
                                      onsubmit="return confirm('Delete this question?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                        No questions found. Add your first question or bulk import them.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table></div>

    @if($questions->hasPages())
        <div class="px-5 py-4 border-t border-border flex items-center justify-between">
            <span class="text-xs text-gray-500">
                Showing {{ $questions->firstItem() }}–{{ $questions->lastItem() }} of {{ $questions->total() }} questions
            </span>
            {{ $questions->links('pagination::tailwind') }}
        </div>
    @endif
</div>

</div>{{-- end right col --}}
</div>{{-- end grid --}}

<script>
// Reads an audio question aloud using the browser's text-to-speech.
function speakQuestion(text, btn) {
    if (!('speechSynthesis' in window)) {
        alert('Audio playback is not supported in this browser.');
        return;
    }
    const spoken = String(text)
        .replace(/\+/g, ' plus ')
        .replace(/[−–-]/g, ' minus ')
        .replace(/[x×*]/gi, ' times ')
        .replace(/[÷/]/g, ' divided by ')
        .replace(/=/g, ' equals ')
        .replace(/\?/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    window.speechSynthesis.cancel();
    const utter = new SpeechSynthesisUtterance(spoken);
    utter.rate = 0.9;
    utter.lang = 'en-IN';
    if (btn) {
        btn.classList.add('animate-pulse');
        utter.onend = () => btn.classList.remove('animate-pulse');
        utter.onerror = () => btn.classList.remove('animate-pulse');
    }
    window.speechSynthesis.speak(utter);
}
</script>

@endsection
