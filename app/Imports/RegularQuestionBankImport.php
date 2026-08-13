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
 * correct_answer. No `level` column — questions are never tied
 * to a level directly. category/type must already exist in the taxonomy;
 * unknown values are a row-level error, never auto-created (a typo must
 * surface, not silently pollute the taxonomy).
 */
class RegularQuestionBankImport implements ToCollection, WithHeadingRow
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

            $comboKey = $category->id . '-' . $type->id;
            if (! isset($this->seenByCombo[$comboKey])) {
                $this->seenByCombo[$comboKey] = RegularQuestionBank::where('category_id', $category->id)
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

            $answerFormat = strtolower(trim((string) ($row['answer_format'] ?? 'mcq'))) ?: 'mcq';
            if (! in_array($answerFormat, ['mcq', 'audio'], true)) {
                $this->errors[] = "Row {$line}: answer_format must be 'mcq' or 'audio'.";
                continue;
            }

            $optionA = ($v = trim((string) ($row['option_a'] ?? ''))) === '' ? null : $v;
            $optionB = ($v = trim((string) ($row['option_b'] ?? ''))) === '' ? null : $v;
            $optionC = ($v = trim((string) ($row['option_c'] ?? ''))) === '' ? null : $v;
            $optionD = ($v = trim((string) ($row['option_d'] ?? ''))) === '' ? null : $v;
            $correct = ($v = strtolower(trim((string) ($row['correct_answer'] ?? '')))) === '' ? null : $v;

            if ($answerFormat === 'mcq') {
                if ($optionA === null || $optionB === null) {
                    $this->errors[] = "Row {$line}: MCQ needs at least option_a and option_b.";
                    continue;
                }
                if (! in_array($correct, ['a', 'b', 'c', 'd'], true)) {
                    $this->errors[] = "Row {$line}: correct_answer must be a, b, c or d.";
                    continue;
                }
                $optionFor = ['a' => $optionA, 'b' => $optionB, 'c' => $optionC, 'd' => $optionD];
                if ($optionFor[$correct] === null) {
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
