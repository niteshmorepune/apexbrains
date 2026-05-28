<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Student;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(): View
    {
        $certificates = Certificate::with('student', 'level')
            ->where('franchise_id', Auth::user()->franchise_id)
            ->latest('issued_at')
            ->paginate(20);

        $students = Student::where('is_active', true)->orderBy('first_name')->get();

        return view('franchise.certificates.index', compact('certificates', 'students'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'type'       => ['required', 'in:level_completion,merit,excellence,participation'],
        ]);

        $student    = Student::with('currentLevel')->findOrFail($data['student_id']);
        $franchiseId = Auth::user()->franchise_id;

        $certNumber      = 'CERT-' . strtoupper(Str::random(8));
        $verificationCode = Str::uuid()->toString();

        $certificate = Certificate::create([
            'franchise_id'      => $franchiseId,
            'student_id'        => $student->id,
            'level_id'          => $student->current_level_id,
            'certificate_number'=> $certNumber,
            'verification_code' => $verificationCode,
            'type'              => $data['type'],
            'issued_at'         => now(),
            'issued_by'         => Auth::id(),
            'qr_data'           => route('certificate.verify', $verificationCode),
            'is_revoked'        => false,
        ]);

        AuditLogger::log('certificate_generated', 'Certificate', $certificate->id);

        return redirect()->route('franchise.certificates.download', $certificate)
            ->with('success', "Certificate {$certNumber} generated for {$student->full_name}.");
    }

    public function download(Certificate $certificate)
    {
        $certificate->load('student.currentLevel', 'level', 'issuedBy');

        return view('franchise.certificates.show', compact('certificate'));
    }

    public function downloadPdf(Certificate $certificate): Response
    {
        $certificate->load('student.currentLevel', 'level', 'issuedBy');

        $pdf = Pdf::loadView('franchise.certificates.show', compact('certificate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }
}
