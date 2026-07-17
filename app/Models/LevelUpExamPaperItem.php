<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelUpExamPaperItem extends Model
{
    protected $fillable = [
        'paper_id', 'question_text', 'option_a', 'option_b',
        'option_c', 'option_d', 'correct_answer', 'sort_order',
    ];

    protected $casts = [
        'paper_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(LevelUpExamPaper::class, 'paper_id');
    }
}
