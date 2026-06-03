<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassPracticePaperQuestion extends Model
{
    protected $fillable = ['paper_id', 'question_id', 'sort_order'];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(ClassPracticePaper::class, 'paper_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}
