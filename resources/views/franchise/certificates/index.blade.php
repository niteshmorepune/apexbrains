@extends('layouts.franchise')
@section('title', 'Certificate Generation')

@php
    // Distinct colour per level for the level badge (hex so Alpine can bind it live).
    $levelColors = ['#1A73E8', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4', '#6366F1', '#F97316'];

    // Data for the live Alpine preview (built here to keep arrow-fns out of @json directives).
    $studentData = $students->map(fn ($s) => [
        'id'          => (string) $s->id,
        'name'        => $s->full_name,
        'levelId'     => (string) ($s->current_level_id ?? ''),
        'levelNumber' => $s->currentLevel?->number,
    ])->values();
    $levelData = $levels->map(fn ($l) => [
        'id'     => (string) $l->id,
        'number' => $l->number,
        'title'  => $l->title,
    ])->values();
@endphp

@section('content')

<div x-data="certForm()">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-[13px] text-gray-400 mb-1">
        <a href="{{ route('franchise.dashboard') }}" class="hover:text-gray-600">Franchises</a>
        <span>/</span>
        <a href="{{ route('franchise.reports.index') }}" class="hover:text-gray-600">Progress</a>
        <span>/</span>
        <span class="font-semibold text-gray-700">Certificates</span>
    </nav>
    <h1 class="text-[26px] font-extrabold text-gray-900 mb-5">Certificate Generation</h1>

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    {{-- Top: form + preview --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Generate New Certificate --}}
        <div class="bg-white rounded-2xl border border-border shadow-sm p-6">
            <h2 class="text-base font-bold text-gray-900 mb-5">Generate New Certificate</h2>

            <form method="POST" action="{{ route('franchise.certificates.generate') }}" class="space-y-5">
                @csrf

                {{-- Select Student --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Select Student <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4-4"/>
                        </svg>
                        <select name="student_id" required x-model="studentId" @change="onStudentChange()"
                                class="w-full appearance-none border border-border rounded-xl pl-10 pr-4 py-3 text-sm text-gray-700 bg-bg-light focus:outline-none focus:ring-2 focus:ring-fran focus:bg-white">
                            <option value="">Search student name or ID...</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->full_name }} — Level {{ $s->currentLevel?->number ?? '—' }} — ID: {{ $s->student_code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Certificate Level + badge --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Certificate Level <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <select name="level_id" x-model="levelId" @change="onLevelChange()"
                                    class="w-full appearance-none border border-fran rounded-xl px-4 py-3 text-sm text-fran font-medium bg-blue-50 focus:outline-none focus:ring-2 focus:ring-fran">
                                <option value="">Auto (student's current level)</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}">Level {{ $level->number }} — Completed</option>
                                @endforeach
                            </select>
                            <svg class="w-4 h-4 text-fran absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <span x-show="levelNumber" x-cloak
                              class="flex-shrink-0 px-5 py-2.5 rounded-xl text-white text-sm font-bold"
                              :style="`background:${badgeColor}`"
                              x-text="`L${levelNumber}`"></span>
                    </div>
                </div>

                {{-- Issue date + series --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Issue Date</label>
                        <input type="date" name="issued_at" x-model="issueDate"
                               class="w-full border border-border rounded-xl px-4 py-3 text-sm text-gray-700 bg-bg-light focus:outline-none focus:ring-2 focus:ring-fran focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Certificate Series</label>
                        <input type="text" name="series" value="{{ now()->year }}-A" placeholder="{{ now()->year }}-A"
                               class="w-full border border-border rounded-xl px-4 py-3 text-sm text-gray-500 focus:outline-none focus:ring-2 focus:ring-fran">
                    </div>
                </div>

                {{-- Certificate Type pills --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Certificate Type</label>
                    <input type="hidden" name="type" :value="type">
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['level_completion' => 'Level Completion', 'merit' => 'Merit Award', 'excellence' => 'Excellence Award'] as $val => $lbl)
                            <button type="button" @click="type = '{{ $val }}'"
                                    class="py-2.5 px-2 rounded-xl border text-sm font-medium transition-colors"
                                    :class="type === '{{ $val }}' ? 'bg-blue-50 border-fran text-fran font-semibold' : 'bg-white border-border text-gray-600 hover:border-fran'">
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <button type="submit"
                            class="flex items-center justify-center gap-2 py-3 bg-green-500 text-white rounded-xl text-sm font-semibold hover:bg-green-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Generate and Send
                    </button>
                    <button type="button" @click="scrollToPreview()"
                            class="flex items-center justify-center gap-2 py-3 border border-fran text-fran rounded-xl text-sm font-semibold hover:bg-blue-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S5.5 5 12 5s9.5 7 9.5 7-3 7-9.5 7S2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        Preview Certificate
                    </button>
                </div>
            </form>
        </div>

        {{-- Certificate Preview --}}
        <div class="bg-white rounded-2xl border border-border shadow-sm p-6" x-ref="preview">
            <h2 class="text-base font-bold text-gray-900 mb-4">Certificate Preview</h2>

            <div class="relative bg-white border border-border rounded-xl overflow-hidden shadow-inner aspect-[1.5/1]">
                {{-- Corner ribbons --}}
                <div class="absolute -left-8 -bottom-8 w-24 h-24 rotate-45 bg-gradient-to-tr from-orange-400 to-rose-500"></div>
                <div class="absolute -left-5 -bottom-10 w-24 h-24 rotate-45 bg-fran/80"></div>
                <div class="absolute -right-8 -bottom-8 w-24 h-24 rotate-45 bg-gradient-to-tl from-blue-500 to-cyan-400"></div>

                <div class="relative h-full flex flex-col items-center justify-center text-center px-6 py-5">
                    {{-- Header --}}
                    <div class="flex items-center justify-between w-full mb-1">
                        <div class="text-left leading-tight">
                            <p class="text-lg font-black"><span class="text-rose-500">Apex</span> <span class="text-fran">Brains</span></p>
                            <p class="text-[9px] tracking-[0.3em] text-amber-500 font-semibold">A B A C U S</p>
                        </div>
                        <span class="w-9 h-9 rounded-full border-2 border-gray-300 text-[7px] font-bold text-gray-400 flex items-center justify-center text-center leading-none">ISO<br>9001</span>
                    </div>

                    <p class="text-[13px] font-bold tracking-wide text-gray-800 mt-1">CERTIFICATE OF COMPLETION</p>
                    <p class="text-[9px] text-gray-400 mt-2">This certifies that</p>

                    <p class="text-2xl font-black text-fran my-1" style="font-family: 'Brush Script MT', cursive;"
                       x-text="studentName || 'Student Name'"></p>

                    <p class="text-[9px] text-gray-400">has successfully completed</p>

                    <div class="bg-blue-50 rounded-lg px-4 py-1.5 my-2">
                        <p class="text-[11px] font-bold text-fran" x-text="levelLabel"></p>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-end justify-between w-full mt-auto pt-2">
                        <div class="text-center">
                            <div class="w-16 border-b border-gray-300 mb-0.5"></div>
                            <p class="text-[8px] text-gray-400">Teacher</p>
                        </div>
                        <div class="text-center">
                            <div class="w-7 h-7 mx-auto bg-white rounded grid grid-cols-3 grid-rows-3 gap-px p-0.5">
                                @foreach([1,0,1,0,1,0,1,0,1] as $cell)<span class="{{ $cell ? 'bg-gray-700' : 'bg-transparent' }}"></span>@endforeach
                            </div>
                            <p class="text-[7px] text-gray-400 mt-0.5" x-text="issueDateLabel"></p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 border-b border-gray-300 mb-0.5"></div>
                            <p class="text-[8px] text-gray-400">Director</p>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-400 text-center mt-3" x-text="typeLabel + ' · Live preview'"></p>
        </div>
    </div>

    {{-- Recently Generated Certificates --}}
    <div class="bg-white rounded-2xl border border-border shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-base font-bold text-gray-900">Recently Generated Certificates</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead>
                    <tr class="bg-gray-800 text-white text-left">
                        <th class="px-5 py-3.5 font-semibold">Student</th>
                        <th class="px-4 py-3.5 font-semibold">Level</th>
                        <th class="px-4 py-3.5 font-semibold">Type</th>
                        <th class="px-4 py-3.5 font-semibold">Issue Date</th>
                        <th class="px-4 py-3.5 font-semibold">Status</th>
                        <th class="px-4 py-3.5 font-semibold text-center">QR</th>
                        <th class="px-4 py-3.5 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($certificates as $cert)
                        <tr class="hover:bg-bg-light {{ $cert->is_revoked ? 'opacity-50' : '' }}">
                            <td class="px-5 py-3.5 font-medium text-gray-800">{{ $cert->student?->full_name }}</td>
                            <td class="px-4 py-3.5">
                                @if($cert->level)
                                    <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full font-medium">L{{ $cert->level->number }}</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 capitalize text-gray-600">{{ str_replace('_', ' ', $cert->type) }}</td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $cert->issued_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3.5">
                                @switch($cert->status)
                                    @case('revoked')
                                        <span class="text-xs bg-red-50 text-red-600 px-2.5 py-1 rounded-full font-medium">Revoked</span>
                                        @break
                                    @case('sent')
                                        <span class="text-xs bg-green-50 text-green-600 px-2.5 py-1 rounded-full font-medium">Sent</span>
                                        @break
                                    @default
                                        <span class="text-xs bg-amber-50 text-amber-600 px-2.5 py-1 rounded-full font-medium">Generated</span>
                                @endswitch
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($cert->qr_data)<span class="text-green-600 font-bold">✓</span>@else<span class="text-gray-300">—</span>@endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3 text-xs">
                                    <a href="{{ route('franchise.certificates.pdf', $cert) }}" class="text-fran hover:underline">Download</a>
                                    @if($cert->student?->parent?->whatsapp ?? false)
                                        <a href="https://wa.me/91{{ preg_replace('/\D/', '', $cert->student->parent->whatsapp) }}?text={{ urlencode('Certificate ready for ' . $cert->student->full_name) }}"
                                           target="_blank" class="text-green-600 hover:underline">WhatsApp</a>
                                    @else
                                        <span class="text-gray-300">WhatsApp</span>
                                    @endif
                                    <a href="{{ route('franchise.certificates.download', $cert) }}" target="_blank" class="text-gray-500 hover:underline">Print</a>
                                    @if(!$cert->is_revoked && !$cert->sent_at)
                                        <form method="POST" action="{{ route('franchise.certificates.sent', $cert) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-fran hover:underline">Mark Sent</button>
                                        </form>
                                    @endif
                                    @if(!$cert->is_revoked)
                                        <form method="POST" action="{{ route('franchise.certificates.revoke', $cert) }}"
                                              onsubmit="return confirm('Revoke this certificate?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-red-500 hover:underline">Revoke</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center text-gray-400">No certificates generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($certificates->hasPages())
            <div class="px-5 py-4 border-t border-border">{{ $certificates->links('pagination::tailwind') }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function certForm() {
    return {
        students: @json($studentData),
        levels: @json($levelData),
        colors: @json($levelColors),

        studentId: '',
        levelId: '',
        type: 'level_completion',
        issueDate: '{{ now()->toDateString() }}',

        get student() { return this.students.find(s => s.id === this.studentId) || null; },
        get levelObj() { return this.levels.find(l => l.id === this.levelId) || null; },
        get levelNumber() { return this.levelObj?.number ?? this.student?.levelNumber ?? null; },
        get studentName() { return this.student?.name ?? ''; },
        get badgeColor() {
            const n = this.levelNumber;
            return n ? this.colors[(n - 1) % this.colors.length] : '#9ca3af';
        },
        get levelLabel() {
            const l = this.levelObj;
            if (l) return `Level ${l.number} — ${l.title || 'Abacus Mental Math'}`;
            if (this.student?.levelNumber) return `Level ${this.student.levelNumber} — Abacus Mental Math`;
            return 'Level — Abacus Mental Math';
        },
        get typeLabel() {
            return { level_completion: 'Level Completion', merit: 'Merit Award', excellence: 'Excellence Award' }[this.type] || 'Level Completion';
        },
        get issueDateLabel() {
            if (!this.issueDate) return '';
            const d = new Date(this.issueDate + 'T00:00:00');
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        onStudentChange() {
            // Default the certificate level to the student's current level.
            if (this.student?.levelId) this.levelId = this.student.levelId;
        },
        onLevelChange() {},
        scrollToPreview() {
            this.$refs.preview?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },
    };
}
</script>
@endpush

@endsection
