<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LevelUpExamPaper extends Model
{
    protected $fillable = ['exam_id', 'title', 'total_questions', 'is_active', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
        'total_questions' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LevelUpExamPaperItem::class, 'paper_id');
    }
}
