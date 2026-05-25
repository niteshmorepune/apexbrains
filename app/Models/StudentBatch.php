<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentBatch extends Model
{
    protected $fillable = ['student_id', 'batch_id', 'joined_at', 'left_at'];

    protected $casts = [
        'joined_at' => 'date',
        'left_at' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
