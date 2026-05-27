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
            CompetitionPracticeSeeder::class,
            CompetitionPracticePapersSeeder::class,
        ]);
    }
}
