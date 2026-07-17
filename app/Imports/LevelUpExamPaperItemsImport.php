<?php

namespace App\Imports;

use App\Models\LevelUpExamPaperItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Mirrors CompetitionPaperQuestionsImport exactly. Columns: question_text,
 * option_a..d, correct_answer. One CSV = one exam's paper (the level is
 * already fixed on the parent Exam row).
 */
class LevelUpExamPaperItemsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public array $errors = [];

    public function __construct(private int $paperId)
    {
    }

    public function collection(Collection $rows): void
    {
        $now = now();
        $batch = [];
        $order = 0;

        foreach ($rows as $i => $row) {
            $line = $i + 2;

            $questionText = trim((string) ($row['question_text'] ?? ''));
            if ($questionText === '') {
                continue;
            }

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
                'paper_id' => $this->paperId,
                'question_text' => $questionText,
                'option_a' => $optionA,
                'option_b' => $optionB,
                'option_c' => $optionC,
                'option_d' => $optionD,
                'correct_answer' => $correct,
                'sort_order' => ++$order,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $this->imported++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            LevelUpExamPaperItem::insert($chunk);
        }
    }
}
