<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegularQuestionType extends Model
{
    protected $fillable = ['category_id', 'name', 'sort_order'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RegularQuestionCategory::class, 'category_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(RegularQuestionBank::class, 'type_id');
    }

    public function access(): HasMany
    {
        return $this->hasMany(RegularPracticeAccess::class, 'type_id');
    }
}
