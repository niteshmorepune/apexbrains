<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\Student;
use Illuminate\Support\Str;

class CertificateIssuer
{
    /**
     * Issue a participation certificate for a student in a competition.
     *
     * Enforces the business rules approved in the 2026-06 client meeting:
     *  - Student registration is MANDATORY — returns null if not registered.
     *  - Idempotent — one participation certificate per (student, competition).
     *
     * @param  int|null     $issuedBy  Issuing user id; defaults to the student's own user.
     * @param  string|null  $series    Optional certificate series (manual generation).
     * @param  mixed         $issuedAt  Optional issue date; defaults to now().
     */
    public function issueForCompetition(
        Student $student,
        Competition $competition,
        ?int $issuedBy = null,
        ?string $series = null,
        $issuedAt = null
    ): ?Certificate {
        // Registration is mandatory before a certificate can be generated.
        $registered = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->exists();

        if (! $registered) {
            return null;
        }

        // Idempotent — reuse an existing participation certificate if present.
        $existing = Certificate::where('student_id', $student->id)
            ->where('competition_id', $competition->id)
            ->where('type', 'competition')
            ->first();

        if ($existing) {
            return $existing;
        }

        $verificationCode = Str::uuid()->toString();

        $certificate = Certificate::create([
            'franchise_id'       => $student->franchise_id,
            'student_id'         => $student->id,
            'competition_id'     => $competition->id,
            'certificate_number' => 'CERT-' . strtoupper(Str::random(8)),
            'verification_code'  => $verificationCode,
            'type'               => 'competition',
            'series'             => $series,
            'issued_at'          => $issuedAt ?? now(),
            // Participation certificates are delivered immediately on issue.
            'sent_at'            => now(),
            'issued_by'          => $issuedBy ?? $student->user_id,
            'qr_data'            => route('certificate.verify', $verificationCode),
            'is_revoked'         => false,
        ]);

        \App\Services\AuditLogger::log('certificate_generated', 'Certificate', $certificate->id);

        return $certificate;
    }
}
