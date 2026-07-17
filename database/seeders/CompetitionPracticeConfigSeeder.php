<?php

namespace Database\Seeders;

use App\Models\CompetitionPracticeConfig;
use App\Models\CompetitionPracticeLevel;
use App\Models\CompetitionQuestionCategory;
use App\Models\CompetitionQuestionType;
use App\Models\Level;
use Illuminate\Database\Seeder;

/**
 * Level x Category x Type x Question-Count grid, sourced verbatim from the
 * client's "Competition Practice Types" Excel ("Marks / No Of Sums" column
 * == question_count, 1 mark per question). Also seeds a default 10-minute
 * countdown for every level (the Excel has no duration column).
 */
class CompetitionPracticeConfigSeeder extends Seeder
{
    public function run(): void
    {
        // (level number => [ [category, type, count], ... ])
        $configs = [
            1 => [['Without Partners', '1 Digit - 5 Rows', 100]],
            2 => [
                ['Without Partners', '1 Digit - 5 Rows', 50],
                ['Without Partners', '2 Digit + 1 Digit - 5 Rows', 50],
            ],
            3 => [['Small Partners', '1 Digit - 5 Rows', 100]],
            4 => [['Small & Big Partners', '1 Digit - 5 Rows', 100]],
            5 => [['Without Partners', '1 Digit - 5 Rows', 100]],
            6 => [['Small & Big Partners', '1 Digit - 5 Rows', 100]],
            7 => [['Grouping', '1 Digit - 5 Rows', 100]],
            8 => [
                ['Grouping', '2 Digit = 4 Rows', 50],
                ['Multiplication', '2 Digit X 1 Digit', 50],
            ],
            9 => [
                ['Grouping', '2 Digit - 5 Rows', 50],
                ['Multiplication', '3 Digit X 1 Digit', 25],
                ['Division', '3 Digit / 1 Digit', 25],
            ],
            10 => [
                ['Grouping', '2 Digit - 6 Rows', 50],
                ['Multiplication', '4 Digit X 1 Digit', 25],
                ['Division', '4 Digit / 1 Digit', 25],
            ],
            11 => [
                ['Grouping', '2 Digit - 10 Rows', 50],
                ['Multiplication', '2 Digit X 2 Digit', 25],
                ['Division', '3 & 4 Digit / 2 Digit', 25],
            ],
        ];

        foreach ($configs as $levelNumber => $rows) {
            $level = Level::where('number', $levelNumber)->first();
            if (! $level) {
                continue;
            }

            CompetitionPracticeLevel::firstOrCreate(
                ['level_id' => $level->id],
                ['duration_minutes' => 10]
            );

            foreach ($rows as [$categoryName, $typeName, $count]) {
                $category = CompetitionQuestionCategory::where('name', $categoryName)->first();
                if (! $category) {
                    continue;
                }

                $type = CompetitionQuestionType::where('category_id', $category->id)->where('name', $typeName)->first();
                if (! $type) {
                    continue;
                }

                CompetitionPracticeConfig::firstOrCreate(
                    ['level_id' => $level->id, 'category_id' => $category->id, 'type_id' => $type->id],
                    ['question_count' => $count, 'marks' => $count]
                );
            }
        }
    }
}
