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
            // Seed the approved Question Bank first, then build level-wise
            // Practice Papers (with questions attached) from that pool.
            PracticeQuestionsSeeder::class,
            CompetitionPracticePapersSeeder::class,
        ]);
    }
}
