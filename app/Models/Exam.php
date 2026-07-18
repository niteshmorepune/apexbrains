<?php

namespace App\Models;

use App\Models\Scopes\ExamTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Exam extends Model
{
    protected $fillable = [
        'franchise_id', 'level_id', 'title', 'description', 'duration_minutes',
        'total_questions', 'pass_percentage', 'max_attempts', 'scheduled_at',
        'expires_at', 'is_active', 'created_by',
    ];

    protected $casts = [
        'pass_percentage' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ExamTenantScope());
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function papers(): HasMany
    {
        return $this->hasMany(LevelUpExamPaper::class);
    }

    public function activePaper(): HasOne
    {
        return $this->hasOne(LevelUpExamPaper::class)->where('is_active', true)->latestOfMany();
    }

    /**
     * scheduled_at/expires_at are stored as true UTC instants, but the admin
     * form (and every student/franchise/admin display) works in IST — the
     * app runs on config('app.timezone')=UTC, so display code must convert
     * explicitly rather than printing the raw UTC-labeled value.
     */
    public function getScheduledAtIstAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->scheduled_at?->copy()->timezone('Asia/Kolkata');
    }

    public function getExpiresAtIstAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->expires_at?->copy()->timezone('Asia/Kolkata');
    }
}
