<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionQuestionPaper extends Model
{
    protected $fillable = [
        'competition_id', 'level_id', 'title', 'total_questions',
        'duration_minutes', 'pass_percentage', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'total_questions'  => 'integer',
        'duration_minutes' => 'integer',
        'pass_percentage'  => 'integer',
        'competition_id'   => 'integer',
        'level_id'         => 'integer',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CompetitionQuestionPaperItem::class, 'paper_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CompetitionExamAttempt::class, 'paper_id');
    }
}
