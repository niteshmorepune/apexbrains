<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $fillable = [
        'franchise_id', 'title', 'description', 'competition_type',
        'start_date', 'end_date', 'registration_deadline', 'max_participants',
        'fee_amount', 'is_active', 'is_open_to_external', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'fee_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_open_to_external' => 'boolean',
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
}
