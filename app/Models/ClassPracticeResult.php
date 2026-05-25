<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassPracticeResult extends Model
{
    protected $fillable = ['session_id', 'franchise_id', 'total_questions_shown', 'completed_at'];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassPracticeSession::class, 'session_id');
    }

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }
}
