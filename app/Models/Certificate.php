<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    protected $fillable = [
        'franchise_id', 'student_id', 'level_id', 'exam_attempt_id', 'competition_id',
        'certificate_number', 'verification_code', 'type', 'series', 'issued_at', 'sent_at',
        'issued_by', 'pdf_path', 'qr_data', 'is_revoked',
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
}
