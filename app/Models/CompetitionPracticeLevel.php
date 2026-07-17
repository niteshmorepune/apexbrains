<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionPracticeLevel extends Model
{
    protected $fillable = ['level_id', 'duration_minutes'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }
}
