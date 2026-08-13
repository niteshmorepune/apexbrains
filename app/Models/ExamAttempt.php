<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id', 'student_id', 'franchise_id', 'attempt_number', 'question_ids',
        'started_at', 'submitted_at', 'score', 'percentage', 'is_passed', 'status',
        'tab_switch_count', 'fullscreen_exit_count', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'question_ids' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_passed' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    /**
     * started_at/submitted_at are stored as true UTC instants
     * (config('app.timezone')=UTC), but every panel displays them as if
     * they were local IST — mirrors CompetitionExamAttempt::getSubmittedAtIstAttribute().
     */
    public function getStartedAtIstAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->started_at?->copy()->timezone('Asia/Kolkata');
    }

    public function getSubmittedAtIstAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->submitted_at?->copy()->timezone('Asia/Kolkata');
    }
}
