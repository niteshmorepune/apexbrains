<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionExamAttempt extends Model
{
    protected $fillable = [
        'paper_id', 'competition_id', 'student_id', 'score', 'percentage',
        'status', 'started_at', 'submitted_at', 'ip_address', 'user_agent',
        'tab_switch_count',
    ];

    protected $casts = [
        'score'            => 'integer',
        'percentage'       => 'decimal:2',
        'tab_switch_count' => 'integer',
        'started_at'       => 'datetime',
        'submitted_at'     => 'datetime',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(CompetitionQuestionPaper::class, 'paper_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
