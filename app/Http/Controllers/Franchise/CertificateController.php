<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Competition;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\CertificateIssuer;
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

        $students = Student::with(['currentLevel', 'competitionRegistrations.competition'])
            ->where('is_active', true)->orderBy('first_name')->get();
        $levels   = \App\Models\Level::orderBy('number')->get();

        // Competitions available for external participation certificates.
        $competitions = Competition::where('franchise_id', Auth::user()->franchise_id)
            ->orderByDesc('start_date')->get();

        return view('franchise.certificates.index', compact('certificates', 'students', 'levels', 'competitions'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'     => ['required', 'exists:students,id'],
            'level_id'       => ['nullable', 'exists:levels,id'],
            'competition_id' => ['nullable', 'exists:competitions,id'],
            'issued_at'      => ['nullable', 'date'],
            'series'         => ['nullable', 'string', 'max:50'],
            'type'           => ['required', 'in:level_completion,merit,excellence,competition'],
        ]);

        $student     = Student::with('currentLevel')->findOrFail($data['student_id']);
        $franchiseId = Auth::user()->franchise_id;

        // External (competition-only) students get a participation certificate tied
        // to a competition; internal students get a level-completion certificate.
        $isParticipation = $student->student_type === 'external' || $data['type'] === 'competition';

        if ($isParticipation) {
            if (empty($data['competition_id'])) {
                return back()
                    ->withErrors(['competition_id' => 'Select the competition for this participation certificate.'])
                    ->withInput();
            }
            $competition = Competition::findOrFail($data['competition_id']);
            if ($competition->franchise_id !== $franchiseId) {
                abort(403);
            }

            // Registration is mandatory before a certificate can be generated.
            $certificate = app(CertificateIssuer::class)->issueForCompetition(
                $student, $competition, Auth::id(), $data['series'] ?? null, $data['issued_at'] ?? null
            );

            if (! $certificate) {
                return back()
                    ->withErrors(['competition_id' => "{$student->full_name} is not registered for this competition. Register the student before generating a certificate."])
                    ->withInput();
            }

            return redirect()->route('franchise.certificates.download', $certificate)
                ->with('success', "Certificate {$certificate->certificate_number} generated and sent for {$student->full_name}.");
        }

        // Internal level-completion / merit / excellence certificate.
        $certNumber       = 'CERT-' . strtoupper(Str::random(8));
        $verificationCode = Str::uuid()->toString();

        $certificate = Certificate::create([
            'franchise_id'      => $franchiseId,
            'student_id'        => $student->id,
            'level_id'          => $data['level_id'] ?? $student->current_level_id,
            'competition_id'    => null,
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

    public function download(Certificate $certificate): View
    {
        $certificate->load('student.currentLevel', 'level', 'competition', 'issuedBy');

        return view('franchise.certificates.certificate-document', [
            'certificate' => $certificate,
            'pdf'         => false,
            'logo'        => Certificate::brandLogoDataUri(),
            'pdfUrl'      => route('franchise.certificates.pdf', $certificate),
            'backUrl'     => route('franchise.certificates.index'),
        ]);
    }

    public function revoke(Certificate $certificate): RedirectResponse
    {
        $certificate->update(['is_revoked' => true]);
        AuditLogger::log('certificate_revoked', 'Certificate', $certificate->id);

        return back()->with('success', "Certificate {$certificate->certificate_number} revoked.");
    }

    public function downloadPdf(Certificate $certificate): Response
    {
        $certificate->load('student.currentLevel', 'level', 'competition', 'issuedBy');

        $pdf = Pdf::loadView('franchise.certificates.certificate-document', [
            'certificate' => $certificate,
            'pdf'         => true,
            'logo'        => Certificate::brandLogoDataUri(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }
}
