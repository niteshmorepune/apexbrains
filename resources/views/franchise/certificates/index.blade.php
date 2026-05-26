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
                        <select name="student_id" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="">Select Student</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->full_name }} (L{{ $s->currentLevel?->number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Certificate Type <span class="text-red-500">*</span></label>
                        <select name="type" required
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                            <option value="level_completion">Level Completion</option>
                            <option value="participation">Participation</option>
                            <option value="merit">Merit</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">
                        Generate &amp; Download
                    </button>
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
                            <a href="{{ route('franchise.certificates.download', $cert) }}"
                               class="text-xs text-fran hover:underline">Download</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400">No certificates issued yet.</td>
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
