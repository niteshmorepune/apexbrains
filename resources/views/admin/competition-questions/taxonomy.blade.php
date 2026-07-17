@extends('layouts.admin')
@section('title', 'Competition Question Bank — Categories & Types')
@section('page-title', 'Categories & Types')

@section('page-actions')
    <a href="{{ route('admin.competition-questions.index') }}"
       class="px-4 py-2 border border-border rounded-xl text-sm text-gray-600 hover:bg-bg-light transition-colors">
        ← Back to Question Bank
    </a>
@endsection

@section('content')
@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3 mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 mb-4">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-2xl border border-border p-6 mb-6">
    <h2 class="text-sm font-bold text-admin mb-3">Add category</h2>
    <form method="POST" action="{{ route('admin.competition-questions.taxonomy.categories.store') }}" class="flex items-end gap-3">
        @csrf
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-600 mb-1">Category name</label>
            <input type="text" name="name" required class="w-full border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
        </div>
        <button type="submit" class="px-5 py-2 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark">Add</button>
    </form>
</div>

<div class="space-y-4">
    @foreach($categories as $category)
        <div class="bg-white rounded-2xl border border-border p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-admin">{{ $category->name }}
                    <span class="text-xs font-normal text-gray-400">({{ $category->questions_count }} questions)</span>
                </h3>
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                @forelse($category->types as $type)
                    <span class="inline-flex items-center gap-2 bg-bg-light border border-border rounded-full px-3 py-1 text-xs text-gray-700">
                        {{ $type->name }}
                        <form method="POST" action="{{ route('admin.competition-questions.taxonomy.types.destroy', $type) }}"
                              onsubmit="return confirm('Remove this type?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600">&times;</button>
                        </form>
                    </span>
                @empty
                    <span class="text-xs text-gray-400">No types yet.</span>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.competition-questions.taxonomy.types.store', $category) }}" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <input type="text" name="name" placeholder="e.g. 2 Digit X 1 Digit" required
                           class="w-full border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                </div>
                <button type="submit" class="px-4 py-2 border border-fran text-fran rounded-xl text-sm font-medium hover:bg-fran hover:text-white">Add type</button>
            </form>
        </div>
    @endforeach
</div>
@endsection
