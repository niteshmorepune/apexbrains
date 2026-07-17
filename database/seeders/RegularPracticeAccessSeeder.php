<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\RegularPracticeAccess;
use App\Models\RegularQuestionCategory;
use App\Models\RegularQuestionType;
use Illuminate\Database\Seeder;

/**
 * Level x Category x Type access grid, sourced verbatim from the client's
 * "Regular Practice Sums Type (Digits & Rows)" Excel — one row per level
 * keyed to its Level::number (Junior 1-4 = numbers 1-4, Regular 1-7 =
 * numbers 5-11, per LevelSeeder).
 */
class RegularPracticeAccessSeeder extends Seeder
{
    private const P3 = ['1 Digit - 5 Rows', '1 Digit - 8 Rows', '1 Digit - 10 Rows'];

    private const GROUPING_1D = ['1 Digit - 5 Rows', '1 Digit - 8 Rows', '1 Digit - 10 Rows'];

    private const MULT_ALL = [
        '2 Digit X 1 Digit', '3 Digit X 1 Digit', '4 Digit X 1 Digit',
        '2 Digit X 2 Digit', '3 Digit X 2 Digit',
    ];

    private const DIV_ALL = [
        '3 Digit / 1 Digit', '2 Digit / 1 Digit', '4 Digit / 1 Digit',
        '3 Digit / 2 Digit', '4 Digit / 2 Digit', '5 Digit / 2 Digit',
    ];

    private const DECIMALS_ALL = ['1.2 Numbers - 3 Rows', '1.2 Numbers - 5 Rows'];

    public function run(): void
    {
        $grouping67 = [
            '2 Digit - 3 Rows', '2 Digit - 5 Rows', '2 Digit - 7 Rows', '2 Digit - 10 Rows',
            '3 Digit - 3 Rows', '3 Digit - 4 Rows', '3 Digit - 5 Rows',
        ];

        $access = [
            1 => ['Without Partners' => self::P3],
            2 => ['Without Partners' => self::P3, 'Small Partners' => self::P3],
            3 => ['Without Partners' => self::P3, 'Small Partners' => self::P3, 'Big & Small Partners' => self::P3],
            4 => [
                'Without Partners' => self::P3, 'Small Partners' => self::P3,
                'Big & Small Partners' => self::P3, 'Grouping' => self::GROUPING_1D,
            ],
            5 => ['Without Partners' => self::P3, 'Big & Small Partners' => self::P3],
            6 => [
                'Big & Small Partners' => self::P3,
                'Grouping' => ['1 Digit - 5 Rows', '1 Digit - 8 Rows', '2 Digit - 3 Rows', '2 Digit - 5 Rows'],
            ],
            7 => [
                'Big & Small Partners' => self::P3,
                'Grouping' => [
                    '1 Digit - 5 Rows', '1 Digit - 8 Rows', '1 Digit - 10 Rows',
                    '2 Digit - 3 Rows', '2 Digit - 5 Rows', '2 Digit - 7 Rows',
                ],
            ],
            8 => [
                'Grouping' => ['2 Digit - 3 Rows', '2 Digit - 5 Rows', '2 Digit - 7 Rows', '2 Digit - 10 Rows', '3 Digit - 3 Rows', '3 Digit - 4 Rows'],
                'Multiplication' => ['2 Digit X 1 Digit', '3 Digit X 1 Digit', '4 Digit X 1 Digit'],
                'Division' => ['3 Digit / 1 Digit', '2 Digit / 1 Digit'],
            ],
            9 => [
                'Grouping' => ['2 Digit - 3 Rows', '2 Digit - 5 Rows', '2 Digit - 7 Rows', '2 Digit - 10 Rows', '3 Digit - 3 Rows', '3 Digit - 4 Rows', '3 Digit - 5 Rows'],
                'Multiplication' => ['2 Digit X 1 Digit', '3 Digit X 1 Digit', '4 Digit X 1 Digit', '2 Digit X 2 Digit'],
                'Division' => ['3 Digit / 1 Digit', '2 Digit / 1 Digit', '4 Digit / 1 Digit'],
            ],
            10 => [
                'Grouping' => $grouping67,
                'Multiplication' => self::MULT_ALL,
                'Division' => ['3 Digit / 1 Digit', '2 Digit / 1 Digit', '4 Digit / 1 Digit', '3 Digit / 2 Digit', '4 Digit / 2 Digit'],
                'Decimals' => self::DECIMALS_ALL,
            ],
            11 => [
                'Grouping' => $grouping67,
                'Multiplication' => self::MULT_ALL,
                'Division' => self::DIV_ALL,
                'Decimals' => self::DECIMALS_ALL,
            ],
        ];

        foreach ($access as $levelNumber => $categories) {
            $level = Level::where('number', $levelNumber)->first();
            if (! $level) {
                continue;
            }

            foreach ($categories as $categoryName => $typeNames) {
                $category = RegularQuestionCategory::where('name', $categoryName)->first();
                if (! $category) {
                    continue;
                }

                foreach ($typeNames as $typeName) {
                    $type = RegularQuestionType::where('category_id', $category->id)->where('name', $typeName)->first();
                    if (! $type) {
                        continue;
                    }

                    RegularPracticeAccess::firstOrCreate([
                        'level_id' => $level->id,
                        'type_id' => $type->id,
                    ]);
                }
            }
        }
    }
}
