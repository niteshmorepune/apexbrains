<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassPracticePaper extends Model
{
    protected $fillable = [
        'title', 'description', 'level_id', 'paper_number',
        'total_questions', 'difficulty', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'total_questions' => 'integer',
        'paper_number'    => 'integer',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paperQuestions(): HasMany
    {
        return $this->hasMany(ClassPracticePaperQuestion::class, 'paper_id');
    }
}
