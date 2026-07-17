<?php

namespace App\Imports;

use App\Models\RegularQuestionBank;
use App\Models\RegularQuestionCategory;
use App\Models\RegularQuestionType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Columns: category, type, question_text, answer_format, option_a..d,
 * correct_answer, difficulty. No `level` column — questions are never tied
 * to a level directly. category/type must already exist in the taxonomy;
 * unknown values are a row-level error, never auto-created (a typo must
 * surface, not silently pollute the taxonomy).
 */
class RegularQuestionBankImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $now = now();
        $userId = Auth::id();
        $batch = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2;

            $questionText = trim((string) ($row['question_text'] ?? ''));
            $categoryName = trim((string) ($row['category'] ?? ''));
            $typeName = trim((string) ($row['type'] ?? ''));

            if ($questionText === '' && $categoryName === '' && $typeName === '') {
                continue;
            }

            if ($questionText === '') {
                $this->errors[] = "Row {$line}: question_text is required.";
                continue;
            }

            if ($categoryName === '') {
                $this->errors[] = "Row {$line}: category is required.";
                continue;
            }

            $category = RegularQuestionCategory::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();
            if (! $category) {
                $this->errors[] = "Row {$line}: category '{$categoryName}' not found.";
                continue;
            }

            if ($typeName === '') {
                $this->errors[] = "Row {$line}: type is required.";
                continue;
            }

            $type = RegularQuestionType::where('category_id', $category->id)
                ->whereRaw('LOWER(name) = ?', [strtolower($typeName)])
                ->first();
            if (! $type) {
                $this->errors[] = "Row {$line}: type '{$typeName}' not found under category '{$categoryName}'.";
                continue;
            }

            $answerFormat = strtolower(trim((string) ($row['answer_format'] ?? 'mcq'))) ?: 'mcq';
            if (! in_array($answerFormat, ['mcq', 'audio'], true)) {
                $this->errors[] = "Row {$line}: answer_format must be 'mcq' or 'audio'.";
                continue;
            }

            $difficulty = strtolower(trim((string) ($row['difficulty'] ?? 'medium'))) ?: 'medium';
            if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
                $this->errors[] = "Row {$line}: difficulty must be easy, medium or hard.";
                continue;
            }

            $optionA = trim((string) ($row['option_a'] ?? '')) ?: null;
            $optionB = trim((string) ($row['option_b'] ?? '')) ?: null;
            $optionC = trim((string) ($row['option_c'] ?? '')) ?: null;
            $optionD = trim((string) ($row['option_d'] ?? '')) ?: null;
            $correct = strtolower(trim((string) ($row['correct_answer'] ?? ''))) ?: null;

            if ($answerFormat === 'mcq') {
                if (! $optionA || ! $optionB) {
                    $this->errors[] = "Row {$line}: MCQ needs at least option_a and option_b.";
                    continue;
                }
                if (! in_array($correct, ['a', 'b', 'c', 'd'], true)) {
                    $this->errors[] = "Row {$line}: correct_answer must be a, b, c or d.";
                    continue;
                }
                $optionFor = ['a' => $optionA, 'b' => $optionB, 'c' => $optionC, 'd' => $optionD];
                if (empty($optionFor[$correct])) {
                    $this->errors[] = "Row {$line}: correct_answer '{$correct}' points to an empty option.";
                    continue;
                }
            } else {
                $correct = null;
            }

            $batch[] = [
                'category_id' => $category->id,
                'type_id' => $type->id,
                'question_text' => $questionText,
                'answer_format' => $answerFormat,
                'option_a' => $optionA,
                'option_b' => $optionB,
                'option_c' => $optionC,
                'option_d' => $optionD,
                'correct_answer' => $correct,
                'difficulty' => $difficulty,
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $this->imported++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            RegularQuestionBank::insert($chunk);
        }
    }
}
