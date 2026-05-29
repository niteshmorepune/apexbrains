<?php

namespace App\Models;

use App\Models\Scopes\FranchiseTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'franchise_id', 'user_id', 'student_code', 'student_type',
        'first_name', 'last_name', 'date_of_birth', 'gender', 'photo',
        'address', 'city', 'pincode', 'enrollment_date', 'is_active',
        'current_level_id', 'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function parents(): HasMany
    {
        return $this->hasMany(StudentParent::class);
    }

    public function primaryParent(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StudentParent::class)->where('is_primary', true);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(StudentLevel::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(StudentBatch::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function competitionRegistrations(): HasMany
    {
        return $this->hasMany(CompetitionRegistration::class);
    }

    public function competitionPracticeAttempts(): HasMany
    {
        return $this->hasMany(CompetitionPracticeAttempt::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function practiceSessions(): HasMany
    {
        return $this->hasMany(PracticeSession::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isInternal(): bool
    {
        return $this->student_type === 'internal';
    }

    public function isExternal(): bool
    {
        return $this->student_type === 'external';
    }
}
