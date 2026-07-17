@extends('layouts.admin')
@section('title', 'Competition Practice Configuration')
@section('page-title', 'Competition Practice Configuration')

@section('content')
@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-4">{{ session('error') }}</div>
@endif
@if(session('importErrors') && count(session('importErrors')))
    <div class="bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-xl px-4 py-3 mb-4">
        <p class="font-semibold mb-1">Skipped rows:</p>
        <ul class="list-disc list-inside space-y-0.5 max-h-40 overflow-y-auto">
            @foreach(session('importErrors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-2xl border border-border p-6 mb-6">
    <h2 class="text-sm font-bold text-admin mb-1">Import from Excel</h2>
    <p class="text-xs text-gray-500 mb-4">
        Re-upload the client's "Competition Practice Types" CSV to replace the whole configuration below.
        Competition Practice auto-generates its full question set from these rows — no manual picking by the student.
    </p>
    <form method="POST" action="{{ route('admin.competition-practice-config.store') }}" enctype="multipart/form-data" class="flex items-center gap-3">
        @csrf
        <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
               class="flex-1 text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-fran-light file:text-fran hover:file:bg-fran hover:file:text-white file:cursor-pointer">
        <button type="submit" class="px-5 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark whitespace-nowrap">Upload &amp; Replace</button>
        <a href="{{ route('admin.competition-practice-config.template') }}" class="px-5 py-2.5 border border-fran text-fran rounded-xl text-sm font-medium hover:bg-fran hover:text-white whitespace-nowrap">Template</a>
    </form>
</div>

<div class="space-y-4">
    @foreach($levels as $level)
        <div class="bg-white rounded-2xl border border-border p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-admin">{{ $level->title }}</h3>
                <form method="POST" action="{{ route('admin.competition-practice-levels.update', $level) }}" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <label class="text-xs text-gray-500">Duration (min)</label>
                    <input type="number" name="duration_minutes" min="1" max="180"
                           value="{{ $level->competitionPracticeSetting->duration_minutes ?? 10 }}"
                           class="w-20 border border-border rounded-lg px-2 py-1 text-sm">
                    <button type="submit" class="px-3 py-1 border border-fran text-fran rounded-lg text-xs font-medium hover:bg-fran hover:text-white">Save</button>
                </form>
            </div>

            @if($level->competitionPracticeConfigs->isEmpty())
                <p class="text-xs text-gray-400">No configuration rows yet — upload the Excel above.</p>
            @else
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-border">
                            <th class="py-1.5 pr-4">Category</th>
                            <th class="py-1.5 pr-4">Type</th>
                            <th class="py-1.5 pr-4">Questions</th>
                            <th class="py-1.5 pr-4">Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($level->competitionPracticeConfigs as $config)
                            <tr class="border-b border-border/60">
                                <td class="py-1.5 pr-4">{{ $config->category->name }}</td>
                                <td class="py-1.5 pr-4">{{ $config->type->name }}</td>
                                <td class="py-1.5 pr-4">{{ $config->question_count }}</td>
                                <td class="py-1.5 pr-4">{{ $config->marks }}</td>
                            </tr>
                        @endforeach
                        <tr class="font-semibold text-admin">
                            <td class="py-1.5 pr-4" colspan="2">Total</td>
                            <td class="py-1.5 pr-4">{{ $level->competitionPracticeConfigs->sum('question_count') }}</td>
                            <td class="py-1.5 pr-4">{{ $level->competitionPracticeConfigs->sum('marks') }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
</div>
@endsection
