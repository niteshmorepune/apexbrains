<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionPracticeConfig extends Model
{
    protected $fillable = ['level_id', 'category_id', 'type_id', 'question_count', 'marks'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CompetitionQuestionCategory::class, 'category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CompetitionQuestionType::class, 'type_id');
    }
}
