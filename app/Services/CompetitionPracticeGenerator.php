<?php

namespace App\Services;

use App\Models\CompetitionPracticeConfig;
use Illuminate\Support\Collection;

/**
 * Auto-generates a Competition Practice question set for a level, with no
 * manual category/type/count picking by the student — loops that level's
 * competition_practice_configs rows and pulls question_count random
 * questions per row, concatenated in config-row order.
 */
class CompetitionPracticeGenerator
{
    public function __construct(private CompetitionQuestionPoolService $pool)
    {
    }

    /**
     * @return Collection<int, \App\Models\CompetitionQuestionBank>
     */
    public function generateForLevel(int $levelId): Collection
    {
        $configs = CompetitionPracticeConfig::where('level_id', $levelId)->orderBy('id')->get();

        $questions = collect();

        foreach ($configs as $config) {
            $questions = $questions->merge(
                $this->pool->randomFor($config->category_id, $config->type_id, $config->question_count)
            );
        }

        return $questions;
    }
}
