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
     * Submitted attempts for one level of a competition, ranked (1 = highest).
     * Rank is a persisted column, not a per-request computation, so it can be
     * durably moved by an admin (see Admin\CompetitionController::moveRank())
     * and the same final order is then seen everywhere (Franchise Panel,
     * Student/External result screens, certificates). Any attempt without a
     * rank yet is seeded here — ordered by percentage desc, then lower time
     * taken, then earliest submission (mirrors
     * CompetitionExamAttempt::scopeBetterThan()) — and appended after the
     * current max rank in this level, so seeding never disturbs ranks an
     * admin has already manually rearranged. Ranking is computed across ALL
     * franchises (a "Level-wise Top 3" is a national ranking) — the caller
     * filters to their own franchise's students before generating certificates.
     *
     * @return Collection<int, CompetitionExamAttempt>
     */
    public function rankedAttemptsByLevel(Competition $competition, int $levelId): Collection
    {
        $base = fn () => CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('status', 'submitted')
            ->whereHas('paper', fn ($q) => $q->where('level_id', $levelId));

        $unranked = $base()->whereNull('rank')
            ->orderByDesc('percentage')
            ->orderByRaw(CompetitionExamAttempt::timeTakenSql() . ' ASC')
            ->orderBy('submitted_at')
            ->get();

        if ($unranked->isNotEmpty()) {
            $nextRank = (int) $base()->max('rank') + 1;
            foreach ($unranked as $attempt) {
                $attempt->update(['rank' => $nextRank++]);
            }
        }

        return $base()->with('student.franchise')->orderBy('rank')->get();
    }
}
