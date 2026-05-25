<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        'number', 'title', 'slug', 'description', 'learning_objectives',
        'fee_per_month', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'learning_objectives' => 'array',
        'fee_per_month' => 'decimal:2',
        'is_active' => 'boolean',
        'number' => 'integer',
        'sort_order' => 'integer',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'current_level_id');
    }

    public function studentLevels(): HasMany
    {
        return $this->hasMany(StudentLevel::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class);
    }
}
