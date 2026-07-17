<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegularQuestionCategory extends Model
{
    protected $fillable = ['name', 'sort_order'];

    public function types(): HasMany
    {
        return $this->hasMany(RegularQuestionType::class, 'category_id')->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(RegularQuestionBank::class, 'category_id');
    }
}
