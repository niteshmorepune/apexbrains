@extends('layouts.admin')
@section('title', 'Audio Mental Math Question Generator')
@section('page-title', 'Audio Mental Math Question Generator')

@section('page-actions')
    <a href="{{ route('admin.questions.index') }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
        ← Question Bank
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Generator panel --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-6">
            <h2 class="text-sm font-bold text-admin mb-5">Generate New Audio Question</h2>

            <form method="POST" action="{{ route('admin.questions.audio.generate') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Question Text <span class="text-red-500">*</span></label>
                    <textarea name="question_text" rows="4" required placeholder="Type or paste the abacus question..."
                              class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran resize-none">{{ old('question_text') }}</textarea>
                    @error('question_text')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign to Level <span class="text-red-500">*</span></label>
                    <select name="level_id" required
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <option value="">Select Level</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>Level {{ $level->number }} — {{ $level->title }}</option>
                        @endforeach
                    </select>
                    @error('level_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Voice settings --}}
                <div class="bg-bg-light rounded-xl p-4 mb-5">
                    <p class="text-xs font-semibold text-gray-600 mb-3">Voice Settings</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-600 mb-2">Speed</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['0.75' => '0.75x', '1' => '1x', '1.5' => '1.5x', '2' => '2x'] as $val => $lbl)
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="speed" value="{{ $val }}"
                                               {{ old('speed', '1') === $val ? 'checked' : '' }}
                                               class="accent-fran">
                                        <span class="text-sm">{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-2">Pause Between Numbers</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['none' => 'None', 'short' => 'Short', 'medium' => 'Medium', 'long' => 'Long'] as $val => $lbl)
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="pause" value="{{ $val }}"
                                               {{ old('pause', 'short') === $val ? 'checked' : '' }}
                                               class="accent-fran">
                                        <span class="text-sm">{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-600 mb-2">Voice</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="voice" value="female" checked class="accent-fran">
                                    <span class="text-sm">Female</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="voice" value="male" class="accent-fran">
                                    <span class="text-sm">Male</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Generate Audio Question
                </button>
            </form>
        </div>

    </div>

    {{-- Generated questions list --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden h-fit">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-admin">Generated Audio Questions ({{ $audioQuestions->total() }})</h2>
        </div>
        @forelse($audioQuestions as $q)
            <div class="px-5 py-4 border-b border-border last:border-b-0 hover:bg-bg-light flex items-start gap-4">
                <button type="button"
                        onclick="speakQuestion(@js($q->question_text), this)"
                        class="w-9 h-9 rounded-full bg-fran flex items-center justify-center flex-shrink-0 hover:bg-fran-dark transition-colors"
                        title="Play audio">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                    </svg>
                </button>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-xs font-mono text-gray-400 flex-shrink-0">Q-{{ str_pad($q->id, 4, '0', STR_PAD_LEFT) }}</span>
                        @if($q->level)
                            <span class="text-xs bg-fran-light text-fran px-1.5 py-0.5 rounded-full font-medium">L{{ $q->level->number }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-800 line-clamp-2">{{ $q->question_text }}</p>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        <span>{{ $q->speed ?? '1' }}x speed</span>
                        <span>·</span>
                        <span>{{ $q->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="flex gap-2 flex-shrink-0 items-center">
                    <a href="{{ route('admin.questions.edit', $q) }}"
                       class="text-xs border border-border text-gray-500 px-2.5 py-1 rounded-lg hover:bg-bg-light transition-colors">
                        Save
                    </a>
                    <form method="POST" action="{{ route('admin.questions.destroy', $q) }}"
                          onsubmit="return confirm('Delete this question?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="px-5 py-10 text-center text-gray-400 text-sm">
                No audio questions yet. Generate your first one above.
            </div>
        @endforelse
        @if($audioQuestions->hasPages())
            <div class="px-5 py-4 border-t border-border">
                {{ $audioQuestions->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>

<script>
// Reads a mental-math question aloud using the browser's built-in text-to-speech.
// No server-side audio file is needed — works offline and is free.
function speakQuestion(text, btn) {
    if (!('speechSynthesis' in window)) {
        alert('Audio playback is not supported in this browser.');
        return;
    }

    // Make symbols sound natural (e.g. "2+3=?" → "2 plus 3 equals").
    const spoken = String(text)
        .replace(/\+/g, ' plus ')
        .replace(/[−–-]/g, ' minus ')
        .replace(/[x×*]/gi, ' times ')
        .replace(/[÷/]/g, ' divided by ')
        .replace(/=/g, ' equals ')
        .replace(/\?/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    window.speechSynthesis.cancel(); // stop anything already playing

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
