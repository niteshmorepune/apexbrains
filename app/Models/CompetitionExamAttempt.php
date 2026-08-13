<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionExamAttempt extends Model
{
    protected $fillable = [
        'paper_id', 'competition_id', 'student_id', 'score', 'percentage',
        'status', 'started_at', 'submitted_at', 'ip_address', 'user_agent',
        'tab_switch_count',
    ];

    protected $casts = [
        'score'            => 'integer',
        'percentage'       => 'decimal:2',
        'tab_switch_count' => 'integer',
        'started_at'       => 'datetime',
        'submitted_at'     => 'datetime',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(CompetitionQuestionPaper::class, 'paper_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * submitted_at is stored as a true UTC instant (config('app.timezone')=UTC),
     * but every panel displays it as if it were local IST — mirrors
     * Exam::getScheduledAtIstAttribute().
     */
    public function getSubmittedAtIstAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->submitted_at?->copy()->timezone('Asia/Kolkata');
    }

    /**
     * Time taken to complete the attempt, formatted m:ss. Both timestamps
     * are absolute UTC instants so the diff is timezone-independent.
     */
    public function getTimeTakenAttribute(): ?string
    {
        if (!$this->started_at || !$this->submitted_at) {
            return null;
        }

        return gmdate('i:s', (int) $this->submitted_at->diffInSeconds($this->started_at, true));
    }

    /**
     * Raw seconds elapsed between start and submission, or null if either
     * timestamp is missing. The numeric counterpart to getTimeTakenAttribute(),
     * used for ranking comparisons.
     */
    public function getTimeTakenSecondsAttribute(): ?int
    {
        if (!$this->started_at || !$this->submitted_at) {
            return null;
        }

        return (int) $this->submitted_at->diffInSeconds($this->started_at, true);
    }

    /**
     * The SQL fragment computing time-taken-in-seconds directly from columns,
     * for use in ORDER BY / WHERE clauses where a PHP accessor can't run.
     */
    public static function timeTakenSql(): string
    {
        return 'TIMESTAMPDIFF(SECOND, started_at, submitted_at)';
    }

    /**
     * Ranking tie-break rule used everywhere a Competition Exam is ranked
     * (admin/franchise results, Champion/Winner certificate assignment,
     * a student's own rank on their result screen): higher score wins;
     * if scores tie, lower time taken wins; if that also ties, the earlier
     * submission wins. Scopes the query to attempts strictly better than
     * $reference under this rule.
     */
    public function scopeBetterThan(Builder $query, self $reference): Builder
    {
        $timeTakenSql = self::timeTakenSql();
        $myTimeTaken = $reference->time_taken_seconds;

        return $query->where(function (Builder $q) use ($reference, $timeTakenSql, $myTimeTaken) {
            $q->where('percentage', '>', $reference->percentage);

            if ($myTimeTaken === null) {
                $q->orWhere(function (Builder $q2) use ($reference) {
                    $q2->where('percentage', $reference->percentage)
                       ->where('submitted_at', '<', $reference->submitted_at);
                });

                return;
            }

            $q->orWhere(function (Builder $q2) use ($reference, $timeTakenSql, $myTimeTaken) {
                $q2->where('percentage', $reference->percentage)
                   ->whereRaw("{$timeTakenSql} < ?", [$myTimeTaken]);
            })->orWhere(function (Builder $q2) use ($reference, $timeTakenSql, $myTimeTaken) {
                $q2->where('percentage', $reference->percentage)
                   ->whereRaw("{$timeTakenSql} = ?", [$myTimeTaken])
                   ->where('submitted_at', '<', $reference->submitted_at);
            });
        });
    }
}
