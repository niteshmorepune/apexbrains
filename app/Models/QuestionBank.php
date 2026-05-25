<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'level_id', 'question_text', 'type', 'option_a', 'option_b', 'option_c', 'option_d',
        'correct_answer', 'difficulty', 'status', 'audio_file_path', 'question_category',
        'source_pdf', 'extracted_at', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'extracted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
