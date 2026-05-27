<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Franchise extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'owner_name', 'email', 'phone', 'whatsapp',
        'address', 'city', 'pincode', 'state', 'gst_number', 'pan_number',
        'status', 'rejection_reason', 'franchise_code', 'commission_rate', 'fee_per_student',
        'logo', 'agreed_at',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'fee_per_student' => 'decimal:2',
        'agreed_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(FranchiseSetting::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(FranchiseDocument::class);
    }

    public function classPracticeSessions(): HasMany
    {
        return $this->hasMany(ClassPracticeSession::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
