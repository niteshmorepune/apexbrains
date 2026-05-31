<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\ResourceFile;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceFileController extends Controller
{
    public function index(Request $request): View
    {
        $query = ResourceFile::with('level', 'uploadedBy');

        if ($request->filled('level')) {
            $query->where('level_id', $request->level);
        }
        if ($request->filled('level_group') && str_contains($request->level_group, '-')) {
            [$min, $max] = array_map('intval', explode('-', $request->level_group, 2));
            $query->whereHas('level', fn($q) => $q->whereBetween('number', [$min, $max]));
        }
        if ($request->filled('type')) {
            $query->where('file_type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $files  = $query->latest()->paginate(20)->withQueryString();
        $levels = Level::orderBy('number')->get();

        $stats = [
            'total'  => ResourceFile::count(),
            'pdfs'   => ResourceFile::where('file_type', 'pdf')->count(),
            'images' => ResourceFile::where('file_type', 'image')->count(),
            'size'   => ResourceFile::sum('file_size'),
        ];

        return view('admin.resources.index', compact('files', 'levels', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'    => ['required', 'string', 'max:200'],
            'file'     => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,gif,webp', 'max:51200'], // 50 MB
            'level_id' => ['nullable', 'exists:levels,id'],
        ]);

        $file     = $request->file('file');
        $mime     = $file->getMimeType();
        $fileType = str_contains($mime, 'pdf') ? 'pdf'
                  : (str_contains($mime, 'image') ? 'image' : 'other');

        $path = $file->store('resources', 'public');

        $resource = ResourceFile::create([
            'title'             => $request->title,
            'original_filename' => $file->getClientOriginalName(),
            'file_path'         => $path,
            'mime_type'         => $mime,
            'file_size'         => $file->getSize(),
            'file_type'         => $fileType,
            'level_id'          => $request->level_id ?: null,
            'uploaded_by'       => Auth::id(),
        ]);

        AuditLogger::log('resource_uploaded', 'ResourceFile', $resource->id);

        return redirect()->route('admin.resources.index')
            ->with('success', "'{$resource->title}' uploaded successfully.");
    }

    public function download(ResourceFile $resource): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($resource->file_path), 404);

        return Storage::disk('public')->download(
            $resource->file_path,
            $resource->original_filename
        );
    }

    public function destroy(ResourceFile $resource): RedirectResponse
    {
        Storage::disk('public')->delete($resource->file_path);
        $title = $resource->title;
        $resource->delete();

        AuditLogger::log('resource_deleted', 'ResourceFile', null);

        return redirect()->route('admin.resources.index')
            ->with('success', "'{$title}' deleted.");
    }
}
