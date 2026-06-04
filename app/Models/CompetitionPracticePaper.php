<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionPracticePaper extends Model
{
    protected $fillable = [
        'title', 'description', 'level_id', 'total_questions', 'duration_minutes',
        'difficulty', 'is_active', 'paper_number', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_questions' => 'integer',
        'duration_minutes' => 'integer',
        'paper_number' => 'integer',
        'level_id' => 'integer',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paperQuestions(): HasMany
    {
        return $this->hasMany(CompetitionPaperQuestion::class, 'paper_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CompetitionPracticeAttempt::class, 'paper_id');
    }
}
