<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Competition;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\CertificateImageComposer;
use App\Services\CertificateIssuer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
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
        $certificates = Certificate::with('student', 'level', 'competition', 'examAttempt.exam')
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
            'type'           => ['required', 'in:level_up,level_completion,merit,excellence,competition,participation'],
        ]);

        $student     = Student::with('currentLevel')->findOrFail($data['student_id']);
        $franchiseId = Auth::user()->franchise_id;

        // External (competition-only) students get a participation certificate tied
        // to a competition; internal students get a level-up certificate.
        $isParticipation = $student->student_type === 'external' || in_array($data['type'], ['competition', 'participation'], true);

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
                $student, $competition, Auth::id(), $data['series'] ?? null, $data['issued_at'] ?? null,
                $data['level_id'] ?? $student->current_level_id
            );

            if (! $certificate) {
                return back()
                    ->withErrors(['competition_id' => "{$student->full_name} is not registered for this competition. Register the student before generating a certificate."])
                    ->withInput();
            }

            return redirect()->route('franchise.certificates.download', $certificate)
                ->with('success', "Certificate {$certificate->certificate_number} generated and sent for {$student->full_name}.");
        }

        // A level-up certificate may only be issued once the student has
        // actually PASSED the level-up exam for that level. Merit / excellence
        // certificates are discretionary and not gated.
        $targetLevelId = $data['level_id'] ?? $student->current_level_id;

        $passedExamAttempt = null;

        if (in_array($data['type'], ['level_up', 'level_completion'], true)) {
            $passedExamAttempt = \App\Models\ExamAttempt::where('student_id', $student->id)
                ->where('is_passed', true)
                ->whereHas('exam', fn ($q) => $q->where('level_id', $targetLevelId))
                ->latest('submitted_at')
                ->first();

            if (! $passedExamAttempt) {
                return back()
                    ->withErrors(['level_id' => "{$student->full_name} has not passed the level-up exam for this level yet. A level-up certificate can only be issued after the exam is passed."])
                    ->withInput();
            }
        }

        // Prevent issuing the same certificate (student + level + type) twice.
        $alreadyIssued = Certificate::where('student_id', $student->id)
            ->where('level_id', $targetLevelId)
            ->where('type', $data['type'])
            ->where('is_revoked', false)
            ->exists();

        if ($alreadyIssued) {
            return back()
                ->withErrors(['level_id' => "{$student->full_name} already has a " . str_replace('_', ' ', $data['type']) . " certificate for this level. Revoke the existing one first if you need to reissue it."])
                ->withInput();
        }

        // Internal level-up / merit / excellence certificate.
        $certNumber       = 'CERT-' . strtoupper(Str::random(8));
        $verificationCode = Str::uuid()->toString();

        $certificate = Certificate::create([
            'franchise_id'      => $franchiseId,
            'student_id'        => $student->id,
            'level_id'          => $targetLevelId,
            'competition_id'    => null,
            'exam_attempt_id'   => $passedExamAttempt?->id,
            'certificate_number'=> $certNumber,
            'verification_code' => $verificationCode,
            'type'              => $data['type'],
            'series'            => $data['series'] ?? null,
            'issued_at'         => $data['issued_at'] ?? now(),
            'place'             => Auth::user()->franchise?->city,
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

    /**
     * Live preview of the actual certificate artwork (same GD composer used
     * for the real PDF) for the currently selected form values — no row is
     * persisted. Only the two types reachable from this form (level_up and
     * participation) have bespoke artwork to preview.
     */
    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'     => ['required', 'exists:students,id'],
            'level_id'       => ['nullable', 'exists:levels,id'],
            'competition_id' => ['nullable', 'exists:competitions,id'],
            'type'           => ['required', 'in:level_up,level_completion,merit,excellence,competition,participation'],
            'issued_at'      => ['nullable', 'date'],
        ]);

        $student   = Student::with('currentLevel')->findOrFail($data['student_id']);
        $franchise = Auth::user()->franchise;

        $certificate = new Certificate([
            'franchise_id'   => $franchise?->id,
            'student_id'     => $student->id,
            'level_id'       => $data['level_id'] ?? $student->current_level_id,
            'competition_id' => $data['competition_id'] ?? null,
            'type'           => $data['type'],
            'place'          => $franchise?->city,
            'issued_at'      => $data['issued_at'] ?? now(),
        ]);

        if (! $certificate->usesFlowTemplate() || in_array($certificate->type, ['merit', 'excellence'], true)) {
            return response()->json(['error' => 'No live preview available for this certificate type.'], 422);
        }

        $certificate->setRelation('student', $student);
        $certificate->setRelation('franchise', $franchise);
        $certificate->setRelation('level', $certificate->level_id ? \App\Models\Level::find($certificate->level_id) : null);
        if ($certificate->competition_id) {
            $certificate->setRelation('competition', Competition::find($certificate->competition_id));
        }

        $image = app(CertificateImageComposer::class)->composeDataUri($certificate);

        return response()->json(['image' => $image]);
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
        $certificate->load('student.currentLevel', 'level', 'competition', 'issuedBy', 'franchise');

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
        $certificate->load('student.currentLevel', 'level', 'competition', 'issuedBy', 'franchise');

        $pdf = Pdf::loadView('franchise.certificates.certificate-document', [
            'certificate' => $certificate,
            'pdf'         => true,
            'logo'        => Certificate::brandLogoDataUri(),
        ])->setPaper(...Certificate::paperConfigFor($certificate->type));

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }
}
