<?php

namespace Database\Seeders;

use App\Models\CompetitionPaperQuestion;
use App\Models\CompetitionPracticePaper;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompetitionPracticePapersSeeder extends Seeder
{
    /**
     * Idempotent: no-ops if papers already exist, or if the question bank has no
     * approved questions yet. Creates level-wise practice papers with questions
     * pulled from the approved Question Bank — the single source of practice
     * papers shared by the Student/External practice screens and the Franchise
     * Class Practice catalogue.
     */
    public function run(): void
    {
        if (CompetitionPracticePaper::exists()) {
            return;
        }

        $papersPerLevel    = 5;
        $questionsPerPaper = 20;

        $admin = User::where('email', 'admin@apexbrains.in')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();

        if (! $admin) {
            return;
        }

        $difficulties = ['easy', 'easy', 'medium', 'medium', 'hard'];
        $paperNumber  = 0;

        // Cover every level (Junior 1–4 + Regular 1–7) so testers see the full
        // level-wise practice-paper catalogue.
        $levels = Level::orderBy('number')->get();

        foreach ($levels as $level) {
            // Prefer questions tagged to the level, fall back to the general approved pool.
            $pool = QuestionBank::where('status', 'approved')
                ->where(fn ($q) => $q->where('level_id', $level->id)->orWhereNull('level_id'))
                ->inRandomOrder()
                ->limit($papersPerLevel * $questionsPerPaper)
                ->get();

            if ($pool->isEmpty()) {
                continue;
            }

            for ($n = 1; $n <= $papersPerLevel; $n++) {
                $slice = $pool->slice(($n - 1) * $questionsPerPaper, $questionsPerPaper)->values();

                if ($slice->isEmpty()) {
                    $slice = $pool->take($questionsPerPaper)->values();
                }

                $diff = $difficulties[($n - 1) % count($difficulties)];

                $paper = CompetitionPracticePaper::create([
                    'paper_number'     => ++$paperNumber,
                    'title'            => 'Level ' . $level->number . ' Practice Paper ' . $n . ' — ' . ucfirst($diff),
                    'description'      => 'Abacus practice paper for Level ' . $level->number . '.',
                    'level_id'         => $level->id,
                    'total_questions'  => $slice->count(),
                    'duration_minutes' => 10,
                    'difficulty'       => $diff,
                    'is_active'        => true,
                    'created_by'       => $admin->id,
                ]);

                foreach ($slice as $i => $q) {
                    CompetitionPaperQuestion::create([
                        'paper_id'    => $paper->id,
                        'question_id' => $q->id,
                        'sort_order'  => $i + 1,
                    ]);
                }
            }
        }
    }
}
