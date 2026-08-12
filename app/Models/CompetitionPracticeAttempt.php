<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionPracticeAttempt extends Model
{
    protected $fillable = [
        'level_id', 'question_ids', 'answers', 'student_id', 'started_at', 'submitted_at',
        'score', 'percentage', 'status', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'question_ids' => 'array',
        'answers' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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
