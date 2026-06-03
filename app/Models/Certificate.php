<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
