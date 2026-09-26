<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $fillable = [
        'franchise_id', 'title', 'description',
        'start_date', 'end_date', 'registration_deadline', 'max_participants',
        'fee_amount', 'duration_minutes', 'is_active', 'is_open_to_external', 'created_by',
        'results_declared_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'date',
        'fee_amount' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
        'is_open_to_external' => 'boolean',
        'results_declared_at' => 'datetime',
    ];

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(CompetitionRegistration::class);
    }

    public function questionPapers(): HasMany
    {
        return $this->hasMany(CompetitionQuestionPaper::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(CompetitionExamAttempt::class);
    }

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_ACTIVE      = 'active';
    public const STATUS_ENDED       = 'ended';

    /**
     * Seconds of grace after end_date during which an answer already in
     * flight when the window closed is still accepted (network latency).
     * Starting a new attempt is never allowed after end_date.
     */
    public const ANSWER_GRACE_SECONDS = 30;

    /**
     * start_date/end_date are exact UTC instants (the exam window). Views
     * must display these IST copies, never the raw columns.
     */
    public function getStartDateIstAttribute()
    {
        return $this->start_date?->copy()->timezone('Asia/Kolkata');
    }

    public function getEndDateIstAttribute()
    {
        return $this->end_date?->copy()->timezone('Asia/Kolkata');
    }

    /**
     * Where "now" falls relative to the Competition Exam window. This is the
     * single source of truth for whether the exam can be started or taken —
     * enforced server-side in both the Student and External controllers.
     */
    public function examStatus(): string
    {
        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return self::STATUS_NOT_STARTED;
        }
        if ($this->end_date && $now->gt($this->end_date)) {
            return self::STATUS_ENDED;
        }

        return self::STATUS_ACTIVE;
    }

    public function isExamOpen(): bool
    {
        return $this->examStatus() === self::STATUS_ACTIVE;
    }

    /**
     * Whether an in-progress attempt may still record answers: never before
     * the start, and only up to a short grace after the end (for a request
     * already in flight when the countdown hit the window close).
     */
    public function acceptsAnswers(): bool
    {
        if ($this->examStatus() === self::STATUS_NOT_STARTED) {
            return false;
        }

        return ! $this->end_date || now()->lte($this->end_date->copy()->addSeconds(self::ANSWER_GRACE_SECONDS));
    }

    /**
     * Seconds until the exam window closes, or null when there is no end.
     */
    public function secondsUntilEnd(): ?int
    {
        return $this->end_date ? max(0, (int) now()->diffInSeconds($this->end_date, false)) : null;
    }

    /** "25 Sep 2026, 11:00 AM" in IST. */
    public function windowStartLabel(): ?string
    {
        return $this->start_date_ist?->format('d M Y, h:i A');
    }

    public function windowEndLabel(): ?string
    {
        return $this->end_date_ist?->format('d M Y, h:i A');
    }

    /** "25 Sep 2026, 11:00 AM – 01:00 PM" (end date repeated only if it differs). */
    public function windowLabel(): ?string
    {
        if (! $this->start_date_ist) {
            return null;
        }
        if (! $this->end_date_ist) {
            return $this->windowStartLabel();
        }

        $end = $this->end_date_ist->isSameDay($this->start_date_ist)
            ? $this->end_date_ist->format('h:i A')
            : $this->windowEndLabel();

        return $this->windowStartLabel() . ' – ' . $end;
    }

    /** Student-facing label for examStatus(). */
    public function examStatusLabel(): string
    {
        return match ($this->examStatus()) {
            self::STATUS_NOT_STARTED => 'Not Started',
            self::STATUS_ENDED       => 'Ended',
            default                  => 'Active',
        };
    }

    /** Reason shown when the exam cannot be started right now, else null. */
    public function examUnavailableMessage(): ?string
    {
        return match ($this->examStatus()) {
            self::STATUS_NOT_STARTED => 'This competition has not started yet. The exam opens on ' . $this->windowStartLabel() . ' (IST).',
            self::STATUS_ENDED       => 'This competition has ended. The exam closed on ' . $this->windowEndLabel() . ' (IST).',
            default                  => null,
        };
    }

    /**
     * The competition-level duration takes precedence when the admin has
     * set one; otherwise falls back to the level's question paper duration
     * (the original, pre-2026-08-12 source of truth) so competitions
     * created before this field existed keep working unchanged.
     */
    public function effectiveDurationMinutes(CompetitionQuestionPaper $paper): int
    {
        return $this->duration_minutes ?? $paper->duration_minutes;
    }
}
