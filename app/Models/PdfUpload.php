<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfUpload extends Model
{
    protected $fillable = [
        'franchise_id', 'original_filename', 'stored_path', 'status',
        'questions_extracted', 'error_message', 'uploaded_by', 'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'questions_extracted' => 'integer',
    ];

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
