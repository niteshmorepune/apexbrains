@extends('layouts.franchise')
@section('title', 'Certificates')
@section('page-title', 'Certificate Generation')

@section('content')

<div class="grid grid-cols-3 gap-6">

    {{-- Generate form --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-6">
            <h2 class="text-sm font-bold text-fran mb-4">Generate Certificate</h2>
            <form method="POST" action="{{ route('franchise.certificates.generate') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Student <span class="text-red-500">*</span></label>
                        <input type="text" name="student_search" placeholder="Search student name or ID..."
                               list="studentList"
                               class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran mb-1">
                        <datalist id="studentList">
                            @foreach($students as $s)
                                <option value="{{ $s->full_name }} — L{{ $s->currentLevel?->number ?? '?' }} ({{ $s->student_code }})">
                            @endforeach
                        </datalist>
                        <select name="student_id" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Student</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->full_name }} — L{{ $s->currentLevel?->number ?? '—' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Certificate Level</label>
                        <select name="level_id"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Auto (student's current level)</option>
                            @foreach($levels ?? [] as $level)
                                <option value="{{ $level->id }}">Level {{ $level->number }} — {{ $level->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Issue Date</label>
                            <input type="date" name="issued_at" value="{{ now()->toDateString() }}"
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Certificate Series</label>
                            <input type="text" name="series" value="{{ now()->year }}-A"
                                   class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Certificate Type <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            @foreach(['level_completion' => 'Level Completion', 'merit' => 'Merit Award', 'excellence' => 'Excellence Award', 'participation' => 'Participation'] as $val => $lbl)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="type" value="{{ $val }}"
                                           {{ $val === 'level_completion' ? 'checked' : '' }}
                                           class="accent-fran">
                                    <span class="text-sm text-gray-700">{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit" name="action" value="preview"
                                class="py-2.5 border border-fran text-fran rounded-xl text-sm font-semibold hover:bg-fran-light transition-colors">
                            Preview
                        </button>
                        <button type="submit" name="action" value="generate"
                                class="py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                            Generate &amp; Send
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Preview card --}}
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-fran mb-3">Certificate Preview</h3>
            <div class="border-2 border-dashed border-fran rounded-xl p-4 text-center">
                <div class="w-10 h-10 rounded-full bg-logo-red mx-auto mb-2 flex items-center justify-center text-white font-black text-sm">AB</div>
                <p class="text-xs font-bold text-gray-700">APEX BRAINS ACADEMY</p>
                <p class="text-xs text-gray-400 mt-1">Certificate of Completion</p>
                <p class="text-xs text-gray-300 mt-3">Select student to generate</p>
            </div>
        </div>
    </div>

    {{-- Issued certificates list --}}
    <div class="col-span-2 bg-white rounded-2xl border border-border overflow-hidden">
        <div class="px-5 py-4 border-b border-border">
            <h2 class="text-sm font-semibold text-fran">Issued Certificates</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-fran">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-white">Certificate #</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">Student</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Type</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Issued</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">QR</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($certificates as $cert)
                    <tr class="hover:bg-bg-light {{ $cert->is_revoked ? 'opacity-50' : '' }}">
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $cert->certificate_number }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $cert->student?->full_name }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($cert->level)
                                <span class="text-xs bg-blue-50 text-fran px-2 py-0.5 rounded-full">L{{ $cert->level->number }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs capitalize text-gray-600">{{ str_replace('_', ' ', $cert->type) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $cert->issued_at?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($cert->is_revoked)
                                <span class="text-xs bg-red-50 text-red-500 px-2 py-0.5 rounded-full">Revoked</span>
                            @else
                                <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full font-medium">Generated</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-stu font-bold text-xs">
                            @if($cert->qr_code ?? false) ✓ @else — @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('franchise.certificates.download', $cert) }}"
                                   class="text-xs text-fran hover:underline">Download</a>
                                @if($cert->student?->parent?->whatsapp ?? false)
                                    <a href="https://wa.me/91{{ preg_replace('/\D/', '', $cert->student->parent->whatsapp) }}?text=Certificate+ready+for+{{ urlencode($cert->student->full_name) }}"
                                       target="_blank" class="text-xs text-stu hover:underline">WhatsApp</a>
                                @endif
                                <a href="{{ route('franchise.certificates.download', $cert) }}" target="_blank"
                                   onclick="window.print(); return false;" class="text-xs text-gray-500 hover:underline">Print</a>
                                @if(!$cert->is_revoked)
                                    <form method="POST" action="{{ route('franchise.certificates.revoke', $cert) }}"
                                          onsubmit="return confirm('Revoke this certificate?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs text-red-500 hover:underline">Revoke</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-gray-400">No certificates issued yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($certificates->hasPages())
            <div class="px-5 py-4 border-t border-border">
                {{ $certificates->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>

@endsection
