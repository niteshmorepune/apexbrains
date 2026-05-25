<?php

namespace App\Models;

use App\Models\Scopes\FranchiseTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassPracticeSession extends Model
{
    protected $fillable = [
        'franchise_id', 'batch_id', 'teacher_id', 'title', 'level_id',
        'question_category', 'total_questions', 'time_per_question_seconds',
        'status', 'current_question_index', 'started_at', 'ended_at', 'session_code',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'current_question_index' => 'integer',
        'total_questions' => 'integer',
        'time_per_question_seconds' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new FranchiseTenantScope());
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function sessionQuestions(): HasMany
    {
        return $this->hasMany(ClassPracticeSessionQuestion::class, 'session_id')->orderBy('sort_order');
    }

    public function result(): HasOne
    {
        return $this->hasOne(ClassPracticeResult::class, 'session_id');
    }

    public function currentQuestion(): ?QuestionBank
    {
        $sessionQuestion = $this->sessionQuestions()
            ->where('sort_order', $this->current_question_index)
            ->with('question')
            ->first();

        return $sessionQuestion?->question;
    }
}
