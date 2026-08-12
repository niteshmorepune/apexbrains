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
        'start_date' => 'date',
        'end_date' => 'date',
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
