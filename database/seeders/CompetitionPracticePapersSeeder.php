<?php

namespace Database\Seeders;

use App\Models\CompetitionPracticePaper;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompetitionPracticePapersSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if already seeded
        if (CompetitionPracticePaper::count() >= 50) return;

        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->first();
        if (!$admin) return;

        $difficulties = ['easy', 'easy', 'easy', 'medium', 'medium', 'medium', 'hard', 'hard'];
        $durations    = [5, 8, 10, 12, 15];

        for ($i = 1; $i <= 50; $i++) {
            if (CompetitionPracticePaper::where('paper_number', $i)->exists()) continue;

            $diff = $difficulties[($i - 1) % count($difficulties)];
            $dur  = $durations[($i - 1) % count($durations)];

            CompetitionPracticePaper::create([
                'paper_number'     => $i,
                'title'            => "Practice Paper $i — " . ucfirst($diff),
                'description'      => "Standard abacus competition practice paper with $dur-minute time limit.",
                'total_questions'  => $i <= 20 ? 25 : ($i <= 40 ? 50 : 75),
                'duration_minutes' => $dur,
                'difficulty'       => $diff,
                'is_active'        => true,
                'created_by'       => $admin->id,
            ]);
        }
    }
}
