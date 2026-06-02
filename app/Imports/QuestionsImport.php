<?php

namespace App\Imports;

use App\Models\Level;
use App\Models\QuestionBank;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        // Map level number => id once.
        $levelMap = Level::pluck('id', 'number');
        $now      = now();
        $userId   = Auth::id();
        $batch    = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2; // +1 for heading row, +1 for 1-based index

            $questionText = trim((string) ($row['question_text'] ?? ''));
            $levelRaw     = trim((string) ($row['level'] ?? ''));

            // Skip completely blank rows silently.
            if ($questionText === '' && $levelRaw === '') {
                continue;
            }

            if ($questionText === '') {
                $this->errors[] = "Row {$line}: question_text is required.";
                continue;
            }

            // Level (by level number) — optional but must be valid if provided.
            $levelId = null;
            if ($levelRaw !== '') {
                $levelNum = (int) $levelRaw;
                if (! $levelMap->has($levelNum)) {
                    $this->errors[] = "Row {$line}: level '{$levelRaw}' does not exist.";
                    continue;
                }
                $levelId = $levelMap->get($levelNum);
            }

            // Type
            $type = strtolower(trim((string) ($row['type'] ?? 'mcq'))) ?: 'mcq';
            if (! in_array($type, ['mcq', 'audio'], true)) {
                $this->errors[] = "Row {$line}: type must be 'mcq' or 'audio'.";
                continue;
            }

            // Difficulty
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

            if ($type === 'mcq') {
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
                // Audio questions have no MCQ answer.
                $correct = null;
            }

            $batch[] = [
                'level_id'          => $levelId,
                'question_text'     => $questionText,
                'type'              => $type,
                'option_a'          => $optionA,
                'option_b'          => $optionB,
                'option_c'          => $optionC,
                'option_d'          => $optionD,
                'correct_answer'    => $correct,
                'difficulty'        => $difficulty,
                'question_category' => trim((string) ($row['question_category'] ?? '')) ?: null,
                'status'            => 'approved',
                'approved_by'       => $userId,
                'approved_at'       => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
            $this->imported++;
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            QuestionBank::insert($chunk);
        }
    }
}
