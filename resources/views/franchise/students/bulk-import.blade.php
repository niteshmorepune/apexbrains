@extends('layouts.franchise')
@section('title', 'Bulk Student Import')
@section('page-title', 'Bulk Student Import')

@section('breadcrumb')
    <a href="{{ route('franchise.students.index') }}" class="text-fran hover:underline">Students</a>
    <span class="mx-1 text-gray-400">/</span>
    <span>Bulk Import</span>
@endsection

@section('page-actions')
    <a href="{{ route('franchise.students.index') }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
        ← Back to Students
    </a>
@endsection

@section('content')

@php $preview = $preview ?? session('import_preview'); @endphp

{{-- Step indicator --}}
<div class="flex items-center gap-0 mb-6">
    @php $step = $preview ? 3 : 1; @endphp
    @foreach([1 => 'Download Template', 2 => 'Upload CSV', 3 => 'Review & Confirm'] as $s => $label)
        <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                    {{ $s <= $step ? 'bg-fran text-white' : 'bg-bg-mid text-gray-400' }}">{{ $s }}</div>
                <span class="text-sm {{ $s <= $step ? 'text-fran font-semibold' : 'text-gray-400' }}">{{ $label }}</span>
            </div>
            @if(!$loop->last)<div class="flex-1 h-px bg-border mx-4"></div>@endif
        </div>
    @endforeach
</div>

@if(!$preview)
    <div class="grid grid-cols-2 gap-6">

        {{-- Step 1: Download Template --}}
        <div class="bg-white rounded-2xl border border-border p-6">
            <h2 class="text-sm font-bold text-admin mb-2">Step 1 — Download CSV Template</h2>
            <p class="text-xs text-gray-500 mb-4">Download the template, fill in student data, then upload below.</p>
            <a href="{{ route('franchise.students.import.template') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Download Template
            </a>
            <div class="mt-4 bg-bg-light rounded-xl p-3 text-xs text-gray-500">
                <p class="font-semibold mb-1">Required columns:</p>
                <p>Name, DOB (YYYY-MM-DD), Gender, Parent Name, Mobile, Level (1–14)</p>
            </div>
        </div>

        {{-- Step 2: Upload --}}
        <div class="bg-white rounded-2xl border border-border p-6">
            <h2 class="text-sm font-bold text-admin mb-2">Step 2 — Upload Filled CSV</h2>
            <p class="text-xs text-gray-500 mb-4">Max 500 students per file. CSV format only.</p>
            <form method="POST" action="{{ route('franchise.students.import') }}" enctype="multipart/form-data">
                @csrf
                <label for="csv_file"
                       class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-border rounded-xl cursor-pointer hover:border-fran hover:bg-blue-50 transition-colors mb-4"
                       id="dropzone">
                    <div class="text-3xl mb-1">📊</div>
                    <p class="text-sm text-gray-500">Drag and Drop CSV file here</p>
                    <p class="text-xs text-gray-400 mt-1">or <span class="text-fran font-medium">click to browse</span></p>
                    <p class="text-xs text-gray-300 mt-1">Max 500 students per import</p>
                    <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt" class="hidden"
                           onchange="document.getElementById('fname').textContent = this.files[0]?.name || ''">
                </label>
                <p id="fname" class="text-xs text-fran mb-3 truncate"></p>
                @error('csv_file')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror
                <button type="submit"
                        class="w-full py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Upload &amp; Preview
                </button>
            </form>
        </div>

    </div>
@else
    {{-- Step 3: Review & Confirm --}}
    @php
        $counts = $preview['counts'];
        $rows   = $preview['rows'];
    @endphp

    {{-- Summary cards --}}
    <div class="grid grid-cols-3 gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-stu p-4 text-center">
            <p class="text-3xl font-bold text-stu">{{ $counts['valid'] }}</p>
            <p class="text-sm text-gray-600 mt-1">Valid Rows</p>
        </div>
        <div class="bg-white rounded-2xl border border-red-200 p-4 text-center">
            <p class="text-3xl font-bold text-red-500">{{ $counts['errors'] }}</p>
            <p class="text-sm text-gray-600 mt-1">Error Rows</p>
        </div>
        <div class="bg-white rounded-2xl border border-logo-amber p-4 text-center">
            <p class="text-3xl font-bold text-logo-amber">{{ $counts['duplicate'] }}</p>
            <p class="text-sm text-gray-600 mt-1">Duplicates</p>
        </div>
    </div>

    {{-- Preview table --}}
    <div class="bg-white rounded-2xl border border-border overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-border flex items-center justify-between">
            <h2 class="text-sm font-semibold text-admin">CSV Preview — {{ count($rows) }} rows</h2>
            <a href="{{ route('franchise.students.import.page') }}"
               class="text-xs text-gray-500 hover:underline">Upload a different file</a>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-admin">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white w-12">Row</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">Name</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">Mobile</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-white">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-white">Issue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach($rows as $row)
                    <tr class="hover:bg-bg-light {{ $row['status'] === 'error' ? 'bg-red-50' : ($row['status'] === 'duplicate' ? 'bg-yellow-50' : '') }}">
                        <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $row['row'] }}</td>
                        <td class="px-4 py-2.5 font-medium text-admin">{{ $row['name'] }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full font-medium">L{{ $row['level'] }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $row['mobile'] }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @if($row['status'] === 'valid')
                                <span class="text-xs bg-stu-light text-stu-dark px-2 py-0.5 rounded-full font-medium">Valid</span>
                            @elseif($row['status'] === 'duplicate')
                                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium">Duplicate</span>
                            @else
                                <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full font-medium">Error</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-xs text-gray-400">{{ $row['issue'] ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($counts['valid'] > 0)
        <form method="POST" action="{{ route('franchise.students.import') }}" class="flex items-center gap-3">
            @csrf
            <input type="hidden" name="confirm_import" value="1">
            <button type="submit"
                    class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                Import {{ $counts['valid'] }} Students
            </button>
            <a href="{{ route('franchise.students.import.page') }}"
               class="px-5 py-2.5 border border-border text-gray-600 rounded-xl text-sm hover:bg-bg-light transition-colors">
                Cancel
            </a>
        </form>
    @else
        <p class="text-sm text-red-500">No valid rows to import. Please fix your CSV file and try again.</p>
        <a href="{{ route('franchise.students.import.page') }}"
           class="inline-block mt-3 px-5 py-2.5 border border-border text-gray-600 rounded-xl text-sm hover:bg-bg-light transition-colors">
            Try Again
        </a>
    @endif
@endif

@endsection
