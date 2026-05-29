@extends('layouts.admin')
@section('title', 'Book and Resource Library')
@section('page-title', 'Book and Resource Library')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-admin">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Files</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-red-600">{{ number_format($stats['pdfs']) }}</p>
        <p class="text-xs text-gray-500 mt-1">PDFs</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-stu">{{ number_format($stats['images']) }}</p>
        <p class="text-xs text-gray-500 mt-1">Images</p>
    </div>
    <div class="bg-white rounded-2xl border border-border p-4 text-center">
        <p class="text-2xl font-bold text-fran">
            @php
                $bytes = $stats['size'];
                echo $bytes >= 1048576 ? round($bytes/1048576, 1).' MB' : round($bytes/1024, 1).' KB';
            @endphp
        </p>
        <p class="text-xs text-gray-500 mt-1">Total Size</p>
    </div>
</div>

<div class="grid grid-cols-3 gap-6">

    {{-- Upload Form --}}
    <div class="col-span-1">
        <div class="bg-white rounded-2xl border border-border p-6">
            <h2 class="text-sm font-bold text-admin mb-4">Upload New File</h2>

            <form method="POST" action="{{ route('admin.resources.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="e.g. Level 5 Practice Sheet"
                           class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Level (optional)</label>
                    <select name="level_id"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                        <option value="">All Levels</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>
                                Level {{ $level->number }} — {{ $level->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">File <span class="text-red-500">*</span></label>
                    <label for="file_upload"
                           class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-border rounded-xl cursor-pointer hover:border-fran hover:bg-blue-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-xs text-gray-500">PDF, JPG, PNG — max <strong>50 MB</strong></p>
                        <input id="file_upload" name="file" type="file"
                               accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" class="hidden"
                               onchange="document.getElementById('file-name').textContent = this.files[0]?.name || ''">
                    </label>
                    <p id="file-name" class="text-xs text-fran mt-1 truncate"></p>
                    @error('file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-fran text-white rounded-xl text-sm font-semibold hover:bg-fran-dark transition-colors">
                    Upload File
                </button>
            </form>
        </div>
    </div>

    {{-- File List --}}
    <div class="col-span-2">

        {{-- Search + pill-tab level filter --}}
        <div class="bg-white rounded-2xl border border-border p-4 mb-4 space-y-3">
            <form method="GET" action="{{ route('admin.resources.index') }}" class="flex items-center gap-3">
                <input type="hidden" name="level_group" value="{{ request('level_group') }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search files..."
                       class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran flex-1">
                <select name="type" class="border border-border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-fran">
                    <option value="">All Types</option>
                    <option value="pdf" @selected(request('type') === 'pdf')>PDF</option>
                    <option value="image" @selected(request('type') === 'image')>Image</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-fran text-white rounded-xl text-sm font-semibold">Search</button>
            </form>
            <div class="flex gap-2 flex-wrap">
                @foreach(['' => 'All', '1-3' => 'L1–3', '4-6' => 'L4–6', '7-10' => 'L7–10', '11-14' => 'L11–14'] as $group => $label)
                    <a href="{{ route('admin.resources.index', array_merge(request()->except('level_group', 'page'), $group ? ['level_group' => $group] : [])) }}"
                       class="px-3 py-1 rounded-full text-xs font-medium border transition-colors
                              {{ request('level_group', '') === $group
                                 ? 'bg-fran text-white border-fran'
                                 : 'border-border text-gray-600 hover:border-fran' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
                <h2 class="text-sm font-semibold text-admin">Files ({{ number_format($files->total()) }})</h2>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-admin">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-white">File</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Level</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Type</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-white">Size</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Uploaded</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">By</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-white">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($files as $file)
                        <tr class="hover:bg-bg-light">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    @if($file->file_type === 'pdf')
                                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-bold text-red-600">PDF</span>
                                        </div>
                                    @elseif($file->file_type === 'image')
                                        <div class="w-8 h-8 rounded-lg bg-stu-light flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-bold text-stu">IMG</span>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-bg-mid flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs text-gray-500">FILE</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-admin text-sm truncate max-w-48">{{ $file->title }}</p>
                                        <p class="text-xs text-gray-400 truncate max-w-48">{{ $file->original_filename }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($file->level)
                                    <span class="text-xs bg-fran-light text-fran px-2 py-0.5 rounded-full font-medium">L{{ $file->level->number }}</span>
                                @else
                                    <span class="text-xs text-gray-400">All</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs capitalize {{ $file->file_type === 'pdf' ? 'text-red-600' : ($file->file_type === 'image' ? 'text-stu' : 'text-gray-500') }}">
                                    {{ $file->file_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-gray-500">
                                {{ $file->formatted_size }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">
                                {{ $file->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">
                                {{ $file->uploadedBy?->name ?? 'Admin' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('admin.resources.download', $file) }}"
                                       class="text-xs text-fran hover:underline font-medium">Download</a>
                                    @if($file->file_type === 'pdf' || $file->file_type === 'image')
                                        <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                                           class="text-xs text-gray-500 hover:underline">Preview</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.resources.destroy', $file) }}"
                                          onsubmit="return confirm('Delete this file permanently?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                                No files uploaded yet. Use the form on the left to upload your first resource.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($files->hasPages())
                <div class="px-5 py-4 border-t border-border flex items-center justify-between">
                    <span class="text-xs text-gray-500">
                        Showing {{ $files->firstItem() }}–{{ $files->lastItem() }} of {{ $files->total() }} files
                    </span>
                    {{ $files->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
