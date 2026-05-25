<?php

namespace Database\Seeders;

use App\Models\CompetitionPracticePaper;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompetitionPracticeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('super_admin')->first();

        if (! $admin) {
            return; // Admin must exist before seeding papers
        }

        $difficulties = ['easy', 'medium', 'hard'];

        for ($i = 1; $i <= 50; $i++) {
            CompetitionPracticePaper::firstOrCreate(['paper_number' => $i], [
                'title'           => "Competition Practice Paper {$i}",
                'description'     => "Practice paper {$i} — 50 questions, 10 minutes",
                'total_questions' => 50,
                'duration_minutes'=> 10,
                'difficulty'      => $difficulties[($i - 1) % 3],
                'is_active'       => true,
                'created_by'      => $admin->id,
            ]);
        }
    }
}
