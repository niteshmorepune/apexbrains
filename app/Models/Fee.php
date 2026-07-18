<?php

namespace App\Models;

use App\Models\Scopes\FranchiseTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fee extends Model
{
    protected $fillable = [
        'franchise_id', 'student_id', 'level_id', 'competition_registration_id', 'student_type',
        'amount', 'month', 'due_date', 'status', 'paid_amount', 'fee_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'month' => 'date',
        'due_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new FranchiseTenantScope());
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

    public function competitionRegistration(): BelongsTo
    {
        return $this->belongsTo(CompetitionRegistration::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
