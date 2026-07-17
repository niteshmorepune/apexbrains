<?php

namespace Database\Seeders;

use App\Models\CompetitionQuestionBank;
use App\Models\CompetitionQuestionCategory;
use App\Models\User;
use Database\Seeders\Concerns\GeneratesArithmeticQuestions;
use Illuminate\Database\Seeder;

/**
 * Demo content for the Competition Question Bank, tagged with real
 * category/type ids from CompetitionQuestionTaxonomySeeder. Sized generously
 * (100+ per type) since Competition Practice draws large counts per level
 * (e.g. Junior-1 needs 100 "Without Partners / 1 Digit - 5 Rows" questions).
 */
class CompetitionQuestionBankSeeder extends Seeder
{
    use GeneratesArithmeticQuestions;

    private const QUESTIONS_PER_TYPE = 120;

    public function run(): void
    {
        if (CompetitionQuestionBank::exists()) {
            return;
        }

        $admin = User::where('email', 'admin@apexbrains.in')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();

        $categories = CompetitionQuestionCategory::with('types')->get();
        $seed = 5000;

        foreach ($categories as $category) {
            foreach ($category->types as $type) {
                for ($i = 0; $i < self::QUESTIONS_PER_TYPE; $i++) {
                    $q = $this->generateQuestion($category->name, $type->name, $seed++);

                    CompetitionQuestionBank::create([
                        'category_id' => $category->id,
                        'type_id' => $type->id,
                        'question_text' => $q['question_text'],
                        'option_a' => $q['option_a'],
                        'option_b' => $q['option_b'],
                        'option_c' => $q['option_c'],
                        'option_d' => $q['option_d'],
                        'correct_answer' => $q['correct_answer'],
                        'difficulty' => $q['difficulty'],
                        'status' => 'approved',
                        'approved_by' => $admin?->id,
                        'approved_at' => now(),
                    ]);
                }
            }
        }
    }
}
