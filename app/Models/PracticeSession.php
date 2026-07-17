<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeSession extends Model
{
    protected $fillable = [
        'student_id', 'level_id', 'category_id', 'type_id', 'difficulty', 'total_questions',
        'questions_correct', 'accuracy', 'avg_speed_seconds', 'duration_minutes', 'completed_at',
    ];

    protected $casts = [
        'accuracy' => 'decimal:2',
        'avg_speed_seconds' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RegularQuestionCategory::class, 'category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RegularQuestionType::class, 'type_id');
    }
}
