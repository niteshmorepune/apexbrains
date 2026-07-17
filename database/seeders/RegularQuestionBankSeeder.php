<?php

namespace Database\Seeders;

use App\Models\RegularQuestionBank;
use App\Models\RegularQuestionCategory;
use App\Models\User;
use Database\Seeders\Concerns\GeneratesArithmeticQuestions;
use Illuminate\Database\Seeder;

/**
 * Demo content for the Regular Question Bank, tagged with real category/type
 * ids from RegularQuestionTaxonomySeeder — replaces the old PracticeQuestionsSeeder,
 * whose fake question_category enum has no mapping to the real taxonomy.
 */
class RegularQuestionBankSeeder extends Seeder
{
    use GeneratesArithmeticQuestions;

    private const QUESTIONS_PER_TYPE = 15;

    public function run(): void
    {
        if (RegularQuestionBank::exists()) {
            return;
        }

        $admin = User::where('email', 'admin@apexbrains.in')->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();

        $categories = RegularQuestionCategory::with('types')->get();
        $seed = 1;

        foreach ($categories as $category) {
            foreach ($category->types as $type) {
                for ($i = 0; $i < self::QUESTIONS_PER_TYPE; $i++) {
                    $q = $this->generateQuestion($category->name, $type->name, $seed++);

                    RegularQuestionBank::create([
                        'category_id' => $category->id,
                        'type_id' => $type->id,
                        'question_text' => $q['question_text'],
                        'answer_format' => 'mcq',
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
