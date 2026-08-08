<?php

namespace App\Imports;

use App\Models\CompetitionQuestionBank;
use App\Models\CompetitionQuestionCategory;
use App\Models\CompetitionQuestionType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Columns: category, type, question_text, option_a..d, correct_answer.
 * No answer_format — the Competition bank is MCQ-only. Unknown
 * category/type is a row-level error, never auto-created.
 */
class CompetitionQuestionBankImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public array $errors = [];

    /** @var array<string, array<string, true>> normalized question_text seen per "categoryId-typeId" combo */
    private array $seenByCombo = [];

    private function normalize(string $text): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $text)));
    }

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

            $category = CompetitionQuestionCategory::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();
            if (! $category) {
                $this->errors[] = "Row {$line}: category '{$categoryName}' not found.";
                continue;
            }

            if ($typeName === '') {
                $this->errors[] = "Row {$line}: type is required.";
                continue;
            }

            $type = CompetitionQuestionType::where('category_id', $category->id)
                ->whereRaw('LOWER(name) = ?', [strtolower($typeName)])
                ->first();
            if (! $type) {
                $this->errors[] = "Row {$line}: type '{$typeName}' not found under category '{$categoryName}'.";
                continue;
            }

            $comboKey = $category->id . '-' . $type->id;
            if (! isset($this->seenByCombo[$comboKey])) {
                $this->seenByCombo[$comboKey] = CompetitionQuestionBank::where('category_id', $category->id)
                    ->where('type_id', $type->id)
                    ->pluck('question_text')
                    ->mapWithKeys(fn ($text) => [$this->normalize((string) $text) => true])
                    ->toArray();
            }

            $normalized = $this->normalize($questionText);
            if (isset($this->seenByCombo[$comboKey][$normalized])) {
                $this->errors[] = "Row {$line}: duplicate question — already exists in this category/type.";
                continue;
            }
            $this->seenByCombo[$comboKey][$normalized] = true;

            $optionA = trim((string) ($row['option_a'] ?? '')) ?: null;
            $optionB = trim((string) ($row['option_b'] ?? '')) ?: null;
            $optionC = trim((string) ($row['option_c'] ?? '')) ?: null;
            $optionD = trim((string) ($row['option_d'] ?? '')) ?: null;
            $correct = strtolower(trim((string) ($row['correct_answer'] ?? '')));

            if (! $optionA || ! $optionB) {
                $this->errors[] = "Row {$line}: needs at least option_a and option_b.";
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

            $batch[] = [
                'category_id' => $category->id,
                'type_id' => $type->id,
                'question_text' => $questionText,
                'option_a' => $optionA,
                'option_b' => $optionB,
                'option_c' => $optionC,
                'option_d' => $optionD,
                'correct_answer' => $correct,
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $this->imported++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            CompetitionQuestionBank::insert($chunk);
        }
    }
}
