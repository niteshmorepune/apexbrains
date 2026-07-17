<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegularPracticeAccess extends Model
{
    protected $table = 'regular_practice_access';

    protected $fillable = ['level_id', 'type_id'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RegularQuestionType::class, 'type_id');
    }
}
