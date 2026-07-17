<?php

namespace Database\Seeders;

use App\Models\CompetitionQuestionCategory;
use App\Models\CompetitionQuestionType;
use Illuminate\Database\Seeder;

/**
 * Competition Question Bank taxonomy, sourced verbatim from the client's
 * "Competition Practice Types" Excel. Kept independent from the Regular
 * taxonomy — spellings/type strings differ between the two source sheets
 * (e.g. "2 Digit = 4 Rows" uses "=" not "-"). Flagged in the migration plan
 * for a client spelling pass before go-live.
 */
class CompetitionQuestionTaxonomySeeder extends Seeder
{
    public const CATEGORIES = [
        'Without Partners' => ['1 Digit - 5 Rows', '2 Digit + 1 Digit - 5 Rows'],
        'Small Partners' => ['1 Digit - 5 Rows'],
        'Small & Big Partners' => ['1 Digit - 5 Rows'],
        'Grouping' => ['1 Digit - 5 Rows', '2 Digit = 4 Rows', '2 Digit - 5 Rows', '2 Digit - 6 Rows', '2 Digit - 10 Rows'],
        'Multiplication' => ['2 Digit X 1 Digit', '3 Digit X 1 Digit', '4 Digit X 1 Digit', '2 Digit X 2 Digit'],
        'Division' => ['3 Digit / 1 Digit', '4 Digit / 1 Digit', '3 & 4 Digit / 2 Digit'],
    ];

    public function run(): void
    {
        $catOrder = 0;
        foreach (self::CATEGORIES as $categoryName => $types) {
            $category = CompetitionQuestionCategory::firstOrCreate(
                ['name' => $categoryName],
                ['sort_order' => $catOrder]
            );

            $typeOrder = 0;
            foreach ($types as $typeName) {
                CompetitionQuestionType::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $typeName],
                    ['sort_order' => $typeOrder]
                );
                $typeOrder++;
            }

            $catOrder++;
        }
    }
}
