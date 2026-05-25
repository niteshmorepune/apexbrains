<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionPracticeAttempt extends Model
{
    protected $fillable = [
        'paper_id', 'student_id', 'started_at', 'submitted_at',
        'score', 'percentage', 'status', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(CompetitionPracticePaper::class, 'paper_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
