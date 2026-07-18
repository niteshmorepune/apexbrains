@extends('layouts.admin')
@section('title', 'Import Competition Question Bank')
@section('page-title', 'Import Competition Question Bank')

@section('page-actions')
    <a href="{{ route('admin.competition-questions.index') }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
        ← Back to Question Bank
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-border p-6">
            <h2 class="text-sm font-bold text-admin mb-1">Upload CSV or Excel file</h2>
            <p class="text-xs text-gray-500 mb-4">
                Feeds Competition Practice only. Category and Type are read directly from the file — MCQ-only, no level column.
            </p>

            <form method="POST" action="{{ route('admin.competition-questions.import.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-fran-light file:text-fran hover:file:bg-fran hover:file:text-white file:cursor-pointer">
                <p class="text-xs text-gray-400 mt-2">CSV or Excel (.xlsx) — max 10 MB.</p>
                @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                <div class="mt-5 flex items-center gap-3">
                    <button type="submit"
                            class="px-6 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                        Import Questions
                    </button>
                    <a href="{{ route('admin.competition-questions.import.template') }}"
                       class="px-5 py-2.5 border border-fran text-fran rounded-xl text-sm font-medium hover:bg-fran hover:text-white transition-colors">
                        Download template (CSV)
                    </a>
                    <a href="{{ route('admin.competition-questions.taxonomy') }}"
                       class="px-5 py-2.5 text-sm text-gray-500 hover:text-fran transition-colors">
                        View Category/Type list →
                    </a>
                </div>
            </form>
        </div>

        @if(session('importErrors') && count(session('importErrors')) > 0)
            <div class="bg-white rounded-2xl border border-red-200 p-6">
                <h3 class="text-sm font-bold text-red-600 mb-2">Skipped rows ({{ count(session('importErrors')) }})</h3>
                <ul class="space-y-1 text-xs text-gray-600 max-h-72 overflow-y-auto">
                    @foreach(session('importErrors') as $err)
                        <li class="flex gap-2"><span class="text-red-500">•</span> {{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-border p-5">
            <h3 class="text-sm font-bold text-admin mb-3">Column format</h3>
            <ul class="space-y-2 text-xs text-gray-500">
                <li><span class="font-semibold text-gray-700">category</span> — must match an existing category exactly</li>
                <li><span class="font-semibold text-gray-700">type</span> — must match an existing type under that category</li>
                <li><span class="font-semibold text-gray-700">question_text</span> — required</li>
                <li><span class="font-semibold text-gray-700">option_a … option_d</span> — required</li>
                <li><span class="font-semibold text-gray-700">correct_answer</span> — a, b, c or d</li>
            </ul>
        </div>
    </div>
</div>
@endsection
