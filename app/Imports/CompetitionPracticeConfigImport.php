<?php

namespace App\Imports;

use App\Models\CompetitionPracticeConfig;
use App\Models\CompetitionQuestionCategory;
use App\Models\CompetitionQuestionType;
use App\Models\Level;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Columns: level, category, type, question_count — sourced from the client's
 * Competition Practice Types Excel (marks = question_count, 1 mark/question).
 * Replace-all semantics, same as RegularPracticeAccessImport.
 */
class CompetitionPracticeConfigImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $levelMap = Level::pluck('id', 'number');
        $batch = [];
        $now = now();

        foreach ($rows as $i => $row) {
            $line = $i + 2;

            $levelRaw = trim((string) ($row['level'] ?? ''));
            $categoryName = trim((string) ($row['category'] ?? ''));
            $typeName = trim((string) ($row['type'] ?? ''));
            $countRaw = trim((string) ($row['question_count'] ?? ''));

            if ($levelRaw === '' && $categoryName === '' && $typeName === '') {
                continue;
            }

            $levelNumber = (int) $levelRaw;
            if (! $levelMap->has($levelNumber)) {
                $this->errors[] = "Row {$line}: level '{$levelRaw}' does not exist.";
                continue;
            }

            $category = CompetitionQuestionCategory::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();
            if (! $category) {
                $this->errors[] = "Row {$line}: category '{$categoryName}' not found.";
                continue;
            }

            $type = CompetitionQuestionType::where('category_id', $category->id)
                ->whereRaw('LOWER(name) = ?', [strtolower($typeName)])
                ->first();
            if (! $type) {
                $this->errors[] = "Row {$line}: type '{$typeName}' not found under category '{$categoryName}'.";
                continue;
            }

            $count = (int) $countRaw;
            if ($count < 1) {
                $this->errors[] = "Row {$line}: question_count must be a positive number.";
                continue;
            }

            $batch[] = [
                'level_id' => $levelMap->get($levelNumber),
                'category_id' => $category->id,
                'type_id' => $type->id,
                'question_count' => $count,
                'marks' => $count,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $this->imported++;
        }

        if ($this->imported === 0) {
            return;
        }

        DB::transaction(function () use ($batch) {
            CompetitionPracticeConfig::truncate();
            foreach (array_chunk($batch, 500) as $chunk) {
                CompetitionPracticeConfig::insert($chunk);
            }
        });
    }
}
