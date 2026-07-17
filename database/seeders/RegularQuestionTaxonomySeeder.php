<?php

namespace Database\Seeders;

use App\Models\RegularQuestionCategory;
use App\Models\RegularQuestionType;
use Illuminate\Database\Seeder;

/**
 * Regular Question Bank taxonomy, sourced verbatim from the client's
 * "Regular Practice Sums Type (Digits & Rows)" Excel. Feeds Regular Practice
 * + Class Practice via regular_practice_access.
 */
class RegularQuestionTaxonomySeeder extends Seeder
{
    public const CATEGORIES = [
        'Without Partners' => ['1 Digit - 5 Rows', '1 Digit - 8 Rows', '1 Digit - 10 Rows'],
        'Small Partners' => ['1 Digit - 5 Rows', '1 Digit - 8 Rows', '1 Digit - 10 Rows'],
        'Big & Small Partners' => ['1 Digit - 5 Rows', '1 Digit - 8 Rows', '1 Digit - 10 Rows'],
        'Grouping' => [
            '1 Digit - 5 Rows', '1 Digit - 8 Rows', '1 Digit - 10 Rows',
            '2 Digit - 3 Rows', '2 Digit - 5 Rows', '2 Digit - 7 Rows', '2 Digit - 10 Rows',
            '3 Digit - 3 Rows', '3 Digit - 4 Rows', '3 Digit - 5 Rows',
        ],
        'Multiplication' => [
            '2 Digit X 1 Digit', '3 Digit X 1 Digit', '4 Digit X 1 Digit',
            '2 Digit X 2 Digit', '3 Digit X 2 Digit',
        ],
        'Division' => [
            '3 Digit / 1 Digit', '2 Digit / 1 Digit', '4 Digit / 1 Digit',
            '3 Digit / 2 Digit', '4 Digit / 2 Digit', '5 Digit / 2 Digit',
        ],
        'Decimals' => ['1.2 Numbers - 3 Rows', '1.2 Numbers - 5 Rows'],
    ];

    public function run(): void
    {
        $catOrder = 0;
        foreach (self::CATEGORIES as $categoryName => $types) {
            $category = RegularQuestionCategory::firstOrCreate(
                ['name' => $categoryName],
                ['sort_order' => $catOrder]
            );

            $typeOrder = 0;
            foreach ($types as $typeName) {
                RegularQuestionType::firstOrCreate(
                    ['category_id' => $category->id, 'name' => $typeName],
                    ['sort_order' => $typeOrder]
                );
                $typeOrder++;
            }

            $catOrder++;
        }
    }
}
