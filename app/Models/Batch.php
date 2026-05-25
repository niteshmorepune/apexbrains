<?php

namespace App\Models;

use App\Models\Scopes\FranchiseTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'franchise_id', 'name', 'level_id', 'schedule_days', 'schedule_time', 'max_students', 'is_active',
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new FranchiseTenantScope());
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function studentBatches(): HasMany
    {
        return $this->hasMany(StudentBatch::class);
    }

    public function classPracticeSessions(): HasMany
    {
        return $this->hasMany(ClassPracticeSession::class);
    }
}
