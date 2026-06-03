<?php

namespace Database\Seeders;

use App\Models\ClassPracticePaper;
use App\Models\ClassPracticePaperQuestion;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassPracticePapersSeeder extends Seeder
{
    /**
     * Idempotent: no-ops if papers already exist, or if the question bank has no
     * approved questions yet (so it is safe to call from a migration on prod and
     * again from DatabaseSeeder on fresh installs).
     */
    public function run(): void
    {
        if (ClassPracticePaper::exists()) {
            return;
        }

        $papersPerLevel    = 5;
        $questionsPerPaper  = 20;

        $creatorId = User::where('email', 'admin@apexbrains.in')->value('id')
            ?? User::orderBy('id')->value('id');

        $levels = Level::orderBy('number')->take(6)->get();

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

                $paper = ClassPracticePaper::create([
                    'title'           => 'Abacus Level ' . $level->number . ' (Paper ' . $n . ')',
                    'level_id'        => $level->id,
                    'paper_number'    => $n,
                    'total_questions' => $slice->count(),
                    'difficulty'      => 'easy',
                    'is_active'       => true,
                    'created_by'      => $creatorId,
                ]);

                foreach ($slice as $i => $q) {
                    ClassPracticePaperQuestion::create([
                        'paper_id'    => $paper->id,
                        'question_id' => $q->id,
                        'sort_order'  => $i + 1,
                    ]);
                }
            }
        }
    }
}
