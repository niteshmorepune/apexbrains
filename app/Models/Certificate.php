<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    // Certificate Generation flow types (see Certificate_Generation_Flows doc).
    public const TYPE_LEVEL_UP = 'level_up';

    public const TYPE_PARTICIPATION = 'participation';

    public const TYPE_CHAMPION = 'champion';

    public const TYPE_WINNER = 'winner';

    // Legacy types, kept for historical rows.
    public const TYPE_LEVEL_COMPLETION = 'level_completion';

    public const TYPE_COMPETITION = 'competition';

    protected $fillable = [
        'franchise_id', 'student_id', 'level_id', 'exam_attempt_id', 'competition_id',
        'certificate_number', 'verification_code', 'type', 'rank', 'series', 'issued_at', 'sent_at',
        'place', 'issued_by', 'pdf_path', 'qr_data', 'is_revoked',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'sent_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    /**
     * Lifecycle status: revoked > sent > generated.
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_revoked) {
            return 'revoked';
        }

        return $this->sent_at ? 'sent' : 'generated';
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * The Exam or Competition name this certificate is tied to, for display
     * in certificate lists — falls back to the level name, then nothing for
     * fully discretionary certificates (merit/excellence with no exam link).
     */
    public function getRelatedTitleAttribute(): ?string
    {
        if ($this->competition) {
            return $this->competition->title;
        }

        if ($this->examAttempt?->exam) {
            return $this->examAttempt->exam->title;
        }

        if ($this->level) {
            return $this->level->title . ' Level-Up Exam';
        }

        return null;
    }

    /**
     * The brand logo as a base64 data URI so it embeds reliably in dompdf PDFs
     * and the browser alike. Prefers the uploaded logo, falls back to the bundled mark.
     */
    public static function brandLogoDataUri(): ?string
    {
        $path = public_path('images/apex-logo.png');

        try {
            if (Storage::disk('local')->exists('settings.json')) {
                $settings = json_decode(Storage::disk('local')->get('settings.json'), true) ?? [];
                if (! empty($settings['logo_path']) && Storage::disk('public')->exists($settings['logo_path'])) {
                    $path = Storage::disk('public')->path($settings['logo_path']);
                }
            }
        } catch (\Throwable $e) {
            // fall back to the bundled logo
        }

        if (! is_file($path)) {
            return null;
        }

        $mime = (function_exists('mime_content_type') ? mime_content_type($path) : null) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    /**
     * Whether this certificate uses one of the 4 exact-design templates
     * (Certificate_Generation_Flows) rather than the legacy generic layout.
     */
    public function usesFlowTemplate(): bool
    {
        return in_array($this->type, [
            self::TYPE_LEVEL_UP, self::TYPE_LEVEL_COMPLETION,
            self::TYPE_PARTICIPATION, self::TYPE_COMPETITION,
            self::TYPE_CHAMPION, self::TYPE_WINNER,
        ], true);
    }

    /**
     * The bundled background artwork file for this certificate's type.
     */
    public static function backgroundFileFor(string $type): string
    {
        return match ($type) {
            self::TYPE_LEVEL_UP, self::TYPE_LEVEL_COMPLETION => 'level-up.png',
            self::TYPE_PARTICIPATION, self::TYPE_COMPETITION => 'participation.png',
            self::TYPE_CHAMPION => 'champion.png',
            self::TYPE_WINNER => 'winner.png',
            default => 'participation.png',
        };
    }

    /**
     * dompdf ->setPaper() arguments for this certificate's type. Champion and
     * Winner artwork is a taller custom page size (not standard A4), so the
     * PDF page must match it exactly to reproduce the design without cropping
     * or letterboxing.
     *
     * @return array{0: string|array, 1?: string}
     */
    public static function paperConfigFor(string $type): array
    {
        return match ($type) {
            self::TYPE_CHAMPION, self::TYPE_WINNER => [[0, 0, 790.5, 1119]],
            default => ['a4', 'landscape'],
        };
    }
}
