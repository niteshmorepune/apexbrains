<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassPracticeSessionQuestion extends Model
{
    protected $fillable = ['session_id', 'question_id', 'sort_order'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassPracticeSession::class, 'session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}
