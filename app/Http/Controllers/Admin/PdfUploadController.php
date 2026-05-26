<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdfUpload;
use App\Models\QuestionBank;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PdfUploadController extends Controller
{
    public function index(): View
    {
        $uploads = PdfUpload::with('uploadedBy')
            ->latest()
            ->paginate(15);

        $stats = [
            'total'         => PdfUpload::count(),
            'extracted'     => PdfUpload::where('status', 'processed')->sum('questions_extracted'),
            'pending'       => QuestionBank::where('status', 'pending')->whereNotNull('source_pdf')->count(),
            'bank_ready'    => QuestionBank::where('status', 'approved')->whereNotNull('source_pdf')->count(),
        ];

        return view('admin.pdf-uploads.index', compact('uploads', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $file       = $request->file('pdf_file');
        $filename   = $file->getClientOriginalName();
        $storedPath = $file->store('pdf-uploads', 'local');

        $upload = PdfUpload::create([
            'original_filename'  => $filename,
            'stored_path'        => $storedPath,
            'status'             => 'pending',
            'questions_extracted'=> 0,
            'uploaded_by'        => Auth::id(),
        ]);

        AuditLogger::log('pdf_uploaded', 'PdfUpload', $upload->id);

        return redirect()->route('admin.pdf-uploads.index')
            ->with('success', "PDF \"{$filename}\" uploaded. Processing will begin shortly.");
    }

    public function show(PdfUpload $pdfUpload): View
    {
        $pdfUpload->load('uploadedBy');

        $extractedQuestions = QuestionBank::with('level')
            ->where('source_pdf', $pdfUpload->original_filename)
            ->latest()
            ->get();

        return view('admin.pdf-uploads.show', compact('pdfUpload', 'extractedQuestions'));
    }
}
