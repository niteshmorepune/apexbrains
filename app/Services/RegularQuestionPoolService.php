<?php

namespace App\Services;

use App\Models\RegularPracticeAccess;
use App\Models\RegularQuestionBank;
use App\Models\RegularQuestionCategory;
use App\Models\RegularQuestionType;
use Illuminate\Support\Collection;

/**
 * Shared query surface for Regular Practice + Class Practice, both of which
 * draw from the Regular Question Bank filtered by a level's category/type
 * access grid (regular_practice_access) rather than a level_id on the
 * question itself.
 */
class RegularQuestionPoolService
{
    public function randomFor(int $categoryId, int $typeId, int $count, array $columns = ['*']): Collection
    {
        return RegularQuestionBank::where('status', 'approved')
            ->where('category_id', $categoryId)
            ->where('type_id', $typeId)
            ->inRandomOrder()
            ->limit($count)
            ->get($columns);
    }

    /**
     * Categories the given level has at least one accessible type for.
     */
    public function accessibleCategories(int $levelId): Collection
    {
        $categoryIds = RegularQuestionType::whereIn('id', function ($q) use ($levelId) {
            $q->select('type_id')->from('regular_practice_access')->where('level_id', $levelId);
        })->pluck('category_id')->unique();

        return RegularQuestionCategory::whereIn('id', $categoryIds)->orderBy('sort_order')->get();
    }

    /**
     * Types under a category that the given level has access to.
     */
    public function accessibleTypes(int $levelId, int $categoryId): Collection
    {
        return RegularQuestionType::where('category_id', $categoryId)
            ->whereIn('id', function ($q) use ($levelId) {
                $q->select('type_id')->from('regular_practice_access')->where('level_id', $levelId);
            })
            ->orderBy('sort_order')
            ->get();
    }

    public function hasAccess(int $levelId, int $typeId): bool
    {
        return RegularPracticeAccess::where('level_id', $levelId)->where('type_id', $typeId)->exists();
    }
}
