<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $certificates = Certificate::where('student_id', $student->id)
            ->where('is_revoked', false)
            ->with(['level', 'issuedBy'])
            ->orderByDesc('issued_at')
            ->get();

        return view('external.certificates.index', compact('certificates'));
    }

    public function show(Certificate $certificate): View
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($certificate->student_id !== $student->id) {
            abort(403);
        }

        $certificate->load(['student', 'level', 'issuedBy']);

        return view('external.certificates.show', compact('certificate'));
    }

    public function downloadPdf(Certificate $certificate): Response
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($certificate->student_id !== $student->id) {
            abort(403);
        }

        $certificate->load(['student', 'level', 'issuedBy']);

        $pdf = Pdf::loadView('external.certificates.show-print', compact('certificate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }
}
