<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApexNotification extends Model
{
    protected $table = 'apex_notifications';

    protected $fillable = [
        'franchise_id', 'student_id', 'user_id', 'type', 'title',
        'message', 'data', 'channel', 'is_read', 'read_at', 'sent_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function franchise(): BelongsTo
    {
        return $this->belongsTo(Franchise::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bulk-create one in-app notification per student (exam/competition/result/
     * certificate system events — the franchise "broadcast message" flow in
     * Franchise\NotificationController still creates rows one at a time since
     * it already iterates its target students individually).
     *
     * @param  \Illuminate\Support\Collection<int, Student>  $students
     */
    public static function notifyStudents(iterable $students, string $type, string $title, string $message): void
    {
        $now = now();
        $rows = [];

        foreach ($students as $student) {
            $rows[] = [
                'franchise_id' => $student->franchise_id,
                'student_id'   => $student->id,
                'type'         => $type,
                'title'        => $title,
                'message'      => $message,
                'channel'      => 'app',
                'sent_at'      => $now,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            static::insert($chunk);
        }
    }
}
