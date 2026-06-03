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

        $students = Student::with('currentLevel')->where('is_active', true)->orderBy('first_name')->get();
        $levels   = \App\Models\Level::orderBy('number')->get();

        return view('franchise.certificates.index', compact('certificates', 'students', 'levels'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'level_id'   => ['nullable', 'exists:levels,id'],
            'issued_at'  => ['nullable', 'date'],
            'series'     => ['nullable', 'string', 'max:50'],
            'type'       => ['required', 'in:level_completion,merit,excellence'],
        ]);

        $student    = Student::with('currentLevel')->findOrFail($data['student_id']);
        $franchiseId = Auth::user()->franchise_id;

        $certNumber      = 'CERT-' . strtoupper(Str::random(8));
        $verificationCode = Str::uuid()->toString();

        $certificate = Certificate::create([
            'franchise_id'      => $franchiseId,
            'student_id'        => $student->id,
            'level_id'          => $data['level_id'] ?? $student->current_level_id,
            'certificate_number'=> $certNumber,
            'verification_code' => $verificationCode,
            'type'              => $data['type'],
            'series'            => $data['series'] ?? null,
            'issued_at'         => $data['issued_at'] ?? now(),
            // "Generate and Send" marks the certificate as delivered immediately.
            'sent_at'           => now(),
            'issued_by'         => Auth::id(),
            'qr_data'           => route('certificate.verify', $verificationCode),
            'is_revoked'        => false,
        ]);

        AuditLogger::log('certificate_generated', 'Certificate', $certificate->id);

        return redirect()->route('franchise.certificates.download', $certificate)
            ->with('success', "Certificate {$certNumber} generated and sent for {$student->full_name}.");
    }

    public function markSent(Certificate $certificate): RedirectResponse
    {
        if (! $certificate->is_revoked && ! $certificate->sent_at) {
            $certificate->update(['sent_at' => now()]);
            AuditLogger::log('certificate_sent', 'Certificate', $certificate->id);
        }

        return back()->with('success', "Certificate {$certificate->certificate_number} marked as sent.");
    }

    public function download(Certificate $certificate)
    {
        $certificate->load('student.currentLevel', 'level', 'issuedBy');

        return view('franchise.certificates.show', compact('certificate'));
    }

    public function revoke(Certificate $certificate): RedirectResponse
    {
        $certificate->update(['is_revoked' => true]);
        AuditLogger::log('certificate_revoked', 'Certificate', $certificate->id);

        return back()->with('success', "Certificate {$certificate->certificate_number} revoked.");
    }

    public function downloadPdf(Certificate $certificate): Response
    {
        $certificate->load('student.currentLevel', 'level', 'issuedBy');

        $pdf = Pdf::loadView('franchise.certificates.show', compact('certificate'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }
}
