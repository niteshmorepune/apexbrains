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
}
