<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionQuestionBank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'type_id', 'question_text',
        'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer',
        'status', 'source_file', 'imported_at', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CompetitionQuestionCategory::class, 'category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CompetitionQuestionType::class, 'type_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
