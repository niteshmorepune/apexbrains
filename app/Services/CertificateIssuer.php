<?php

namespace App\Services;

use App\Models\ApexNotification;
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
     * @param  int|null      $levelId   The level the student sat the competition at.
     */
    public function issueForCompetition(
        Student $student,
        Competition $competition,
        ?int $issuedBy = null,
        ?string $series = null,
        $issuedAt = null,
        ?int $levelId = null
    ): ?Certificate {
        // Registration is mandatory before a certificate can be generated.
        $registered = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->exists();

        if (! $registered) {
            return null;
        }

        // Idempotent — reuse an existing participation certificate if present.
        // 'competition' is the legacy type name for the same certificate.
        $existing = Certificate::where('student_id', $student->id)
            ->where('competition_id', $competition->id)
            ->whereIn('type', [Certificate::TYPE_PARTICIPATION, Certificate::TYPE_COMPETITION])
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->create($student, [
            'competition_id' => $competition->id,
            'level_id'       => $levelId,
            'type'           => Certificate::TYPE_PARTICIPATION,
            'series'         => $series,
            'issued_at'      => $issuedAt ?? now(),
            'issued_by'      => $issuedBy ?? $student->user_id,
        ]);
    }

    /**
     * Issue Champion certificates (level-wise rank 1-3) for a competition once
     * results are published. Idempotent per (student, competition) and scoped
     * to the given franchise.
     *
     * @param  \Illuminate\Support\Collection  $rankedAttempts  Attempts (with ->rank set) for one level, already restricted to this franchise's students.
     * @return \Illuminate\Support\Collection<Certificate>
     */
    public function issueChampions($rankedAttempts, Competition $competition, int $levelId, int $issuedBy, ?string $place = null)
    {
        return $rankedAttempts
            ->filter(fn ($attempt) => $attempt->rank >= 1 && $attempt->rank <= 3 && $attempt->student)
            ->map(fn ($attempt) => $this->issueRanked(
                $attempt->student, $competition, $levelId, Certificate::TYPE_CHAMPION, $attempt->rank, $issuedBy, $place
            ));
    }

    /**
     * Issue Winner certificates (level-wise rank 4-6) for a competition once
     * results are published. Idempotent per (student, competition).
     *
     * @param  \Illuminate\Support\Collection  $rankedAttempts
     * @return \Illuminate\Support\Collection<Certificate>
     */
    public function issueWinners($rankedAttempts, Competition $competition, int $levelId, int $issuedBy, ?string $place = null)
    {
        return $rankedAttempts
            ->filter(fn ($attempt) => $attempt->rank >= 4 && $attempt->rank <= 6 && $attempt->student)
            ->map(fn ($attempt) => $this->issueRanked(
                $attempt->student, $competition, $levelId, Certificate::TYPE_WINNER, $attempt->rank, $issuedBy, $place
            ));
    }

    protected function issueRanked(Student $student, Competition $competition, int $levelId, string $type, int $rank, int $issuedBy, ?string $place): Certificate
    {
        $existing = Certificate::where('student_id', $student->id)
            ->where('competition_id', $competition->id)
            ->where('level_id', $levelId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            return $existing;
        }

        $certificate = $this->create($student, [
            'competition_id' => $competition->id,
            'level_id'       => $levelId,
            'type'           => $type,
            'rank'           => $rank,
            'place'          => $place,
            'issued_at'      => now(),
            'issued_by'      => $issuedBy,
        ]);

        $message = $type === Certificate::TYPE_CHAMPION
            ? "🏆 Congratulations! You're Champion — Rank #{$rank} in \"{$competition->title}\"."
            : "🎉 You're a Winner — Rank #{$rank} in \"{$competition->title}\". Congratulations!";

        ApexNotification::notifyStudents(
            [$student],
            $type === Certificate::TYPE_CHAMPION ? 'competition_champion' : 'competition_winner',
            $type === Certificate::TYPE_CHAMPION ? "You're a Champion!" : "You're a Winner!",
            $message
        );

        return $certificate;
    }

    /**
     * @param  array{competition_id?:int,level_id?:int,type:string,rank?:int,series?:?string,place?:?string,issued_at:mixed,issued_by:int}  $attrs
     */
    protected function create(Student $student, array $attrs): Certificate
    {
        $verificationCode = Str::uuid()->toString();

        $certificate = Certificate::create(array_merge([
            'franchise_id'       => $student->franchise_id,
            'student_id'         => $student->id,
            'certificate_number' => 'CERT-' . strtoupper(Str::random(8)),
            'verification_code'  => $verificationCode,
            'place'              => $student->franchise?->city,
            // Certificates are delivered immediately on issue.
            'sent_at'            => now(),
            'qr_data'            => route('certificate.verify', $verificationCode),
            'is_revoked'         => false,
        ], $attrs));

        AuditLogger::log('certificate_generated', 'Certificate', $certificate->id);

        $label = match ($certificate->type) {
            Certificate::TYPE_CHAMPION => 'Champion',
            Certificate::TYPE_WINNER => 'Winner',
            Certificate::TYPE_PARTICIPATION, Certificate::TYPE_COMPETITION => 'Participation',
            default => 'Level Up',
        };

        // Champion/Winner certs get their own richer congratulatory notification
        // (fired from issueRanked() once it has the rank in scope) — skip the
        // generic one here to avoid double-notifying for the same certificate.
        if (! in_array($certificate->type, [Certificate::TYPE_CHAMPION, Certificate::TYPE_WINNER], true)) {
            ApexNotification::notifyStudents(
                [$student],
                'certificate_issued',
                'New Certificate Issued',
                "Your {$label} certificate is ready — view it in your Certificate Vault."
            );
        }

        return $certificate;
    }
}
