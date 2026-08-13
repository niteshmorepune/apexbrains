<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionExamAttempt;
use App\Models\Level;
use Illuminate\Support\Collection;

class CompetitionRankingService
{
    /**
     * The levels this competition has question papers for, in level order.
     */
    public function levelsForCompetition(Competition $competition): Collection
    {
        return Level::whereIn('id', function ($q) use ($competition) {
            $q->select('level_id')
                ->from('competition_question_papers')
                ->where('competition_id', $competition->id);
        })->orderBy('number')->get();
    }

    /**
     * Submitted attempts for one level of a competition, ranked (1 = highest)
     * by percentage, then lower time taken, then earliest submission —
     * mirrors CompetitionExamAttempt::scopeBetterThan(). Ranking is computed
     * across ALL franchises (a "Level-wise Top 3" is a national ranking) —
     * the caller filters to their own franchise's students before generating
     * certificates.
     *
     * @return Collection<int, CompetitionExamAttempt>
     */
    public function rankedAttemptsByLevel(Competition $competition, int $levelId): Collection
    {
        $attempts = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('status', 'submitted')
            ->whereHas('paper', fn ($q) => $q->where('level_id', $levelId))
            ->with('student.franchise')
            ->orderByDesc('percentage')
            ->orderByRaw(CompetitionExamAttempt::timeTakenSql() . ' ASC')
            ->orderBy('submitted_at')
            ->get()
            ->values();

        foreach ($attempts as $i => $attempt) {
            $attempt->rank = $i + 1;
        }

        return $attempts;
    }
}
