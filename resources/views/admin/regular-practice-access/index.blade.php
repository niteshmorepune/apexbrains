@extends('layouts.admin')
@section('title', 'Regular Practice Configuration')
@section('page-title', 'Regular Practice Configuration')

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
        Re-upload the client's "Regular Practice Sums Type" CSV to replace the whole grid below (one row per accessible level/category/type).
        Controls which categories/types each Level can pick for Regular Practice and Class Practice.
    </p>
    <form method="POST" action="{{ route('admin.regular-practice-access.store') }}" enctype="multipart/form-data" class="flex items-center gap-3">
        @csrf
        <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
               class="flex-1 text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-fran-light file:text-fran hover:file:bg-fran hover:file:text-white file:cursor-pointer">
        <button type="submit" class="px-5 py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark whitespace-nowrap">Upload &amp; Replace</button>
        <a href="{{ route('admin.regular-practice-access.template') }}" class="px-5 py-2.5 border border-fran text-fran rounded-xl text-sm font-medium hover:bg-fran hover:text-white whitespace-nowrap">Template</a>
    </form>
</div>

<div class="bg-white rounded-2xl border border-border p-6 overflow-x-auto">
    <h2 class="text-sm font-bold text-admin mb-4">Current access grid</h2>
    <table class="min-w-[900px] w-full text-xs">
        <thead>
            <tr class="text-left text-gray-500 border-b border-border">
                <th class="py-2 pr-4">Level</th>
                @foreach($categories as $category)
                    <th class="py-2 pr-4">{{ $category->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($levels as $level)
                <tr class="border-b border-border/60">
                    <td class="py-2 pr-4 font-semibold text-admin whitespace-nowrap">{{ $level->title }}</td>
                    @foreach($categories as $category)
                        <td class="py-2 pr-4 align-top">
                            <div class="flex flex-wrap gap-1">
                                @foreach($category->types as $type)
                                    @php $granted = isset($accessSet["{$level->id}:{$type->id}"]); @endphp
                                    <span class="px-2 py-0.5 rounded-full {{ $granted ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-300' }}">
                                        {{ $type->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
