<?php

namespace App\Services;

use App\Models\CompetitionQuestionBank;
use Illuminate\Support\Collection;

class CompetitionQuestionPoolService
{
    public function randomFor(int $categoryId, int $typeId, int $count, array $columns = ['*']): Collection
    {
        return CompetitionQuestionBank::where('status', 'approved')
            ->where('category_id', $categoryId)
            ->where('type_id', $typeId)
            ->inRandomOrder()
            ->limit($count)
            ->get($columns);
    }

    /**
     * Level-agnostic random draw across all categories/types — used by
     * External Practice, which has no curriculum level to gate access.
     */
    public function randomAny(int $count, ?string $difficulty = null, array $columns = ['*']): Collection
    {
        return CompetitionQuestionBank::where('status', 'approved')
            ->when($difficulty && $difficulty !== 'all', fn ($q) => $q->where('difficulty', $difficulty))
            ->inRandomOrder()
            ->limit($count)
            ->get($columns);
    }
}
