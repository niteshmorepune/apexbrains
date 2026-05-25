<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FranchiseSetting extends Model
{
    protected $fillable = ['franchise_id', 'key', 'value'];

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }
}
