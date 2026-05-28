<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Level;
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

        $earnedLevelIds = $certificates->pluck('level_id')->filter()->unique()->toArray();
        $currentLevelNum = $student->currentLevel?->number ?? 0;

        $lockedLevels = Level::orderBy('number')
            ->where('number', '>', $currentLevelNum)
            ->whereNotIn('id', $earnedLevelIds)
            ->get();

        return view('student.certificates.index', compact('certificates', 'lockedLevels'));
    }

    public function download(Certificate $certificate): View
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($certificate->student_id !== $student->id) {
            abort(403);
        }

        $certificate->load(['student', 'level', 'issuedBy']);

        return view('franchise.certificates.show', compact('certificate'));
    }

    public function downloadPdf(Certificate $certificate): Response
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($certificate->student_id !== $student->id) {
            abort(403);
        }

        $certificate->load(['student', 'level', 'issuedBy']);

        $pdf = Pdf::loadView('franchise.certificates.show', compact('certificate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }
}
