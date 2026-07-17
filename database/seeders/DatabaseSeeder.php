<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            LevelSeeder::class,
            DemoDataSeeder::class,
            // Taxonomy (Category -> Type) first, then the level-access/config
            // grids that reference it, then bank content tagged to real ids.
            RegularQuestionTaxonomySeeder::class,
            RegularPracticeAccessSeeder::class,
            RegularQuestionBankSeeder::class,
            CompetitionQuestionTaxonomySeeder::class,
            CompetitionPracticeConfigSeeder::class,
            CompetitionQuestionBankSeeder::class,
        ]);
    }
}
