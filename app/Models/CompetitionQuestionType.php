<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionQuestionType extends Model
{
    protected $fillable = ['category_id', 'name', 'sort_order'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CompetitionQuestionCategory::class, 'category_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CompetitionQuestionBank::class, 'type_id');
    }
}
