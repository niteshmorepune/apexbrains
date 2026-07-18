<?php

namespace App\Imports;

use App\Models\Level;
use App\Models\RegularPracticeAccess;
use App\Models\RegularQuestionCategory;
use App\Models\RegularQuestionType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Columns: level, category, type — one row per "YES" cell in the client's
 * Regular Practice Configuration Excel. Replace-all semantics: a successful
 * upload truncates and repopulates the whole grid (each upload represents
 * the Excel's full current state), so it only commits if at least one row parsed.
 */
class RegularPracticeAccessImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $levelByNumber = Level::pluck('id', 'number');
        $levelByTitle = Level::get()->mapWithKeys(fn ($level) => [strtolower($level->title) => $level->id]);
        $batch = [];
        $now = now();

        foreach ($rows as $i => $row) {
            $line = $i + 2;

            $levelRaw = trim((string) ($row['level'] ?? ''));
            $categoryName = trim((string) ($row['category'] ?? ''));
            $typeName = trim((string) ($row['type'] ?? ''));

            if ($levelRaw === '' && $categoryName === '' && $typeName === '') {
                continue;
            }

            $levelId = $levelByTitle->get(strtolower($levelRaw))
                ?? (ctype_digit($levelRaw) ? $levelByNumber->get((int) $levelRaw) : null);
            if (! $levelId) {
                $this->errors[] = "Row {$line}: level '{$levelRaw}' does not exist.";
                continue;
            }

            $category = RegularQuestionCategory::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();
            if (! $category) {
                $this->errors[] = "Row {$line}: category '{$categoryName}' not found.";
                continue;
            }

            $type = RegularQuestionType::where('category_id', $category->id)
                ->whereRaw('LOWER(name) = ?', [strtolower($typeName)])
                ->first();
            if (! $type) {
                $this->errors[] = "Row {$line}: type '{$typeName}' not found under category '{$categoryName}'.";
                continue;
            }

            $batch[] = [
                'level_id' => $levelId,
                'type_id' => $type->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $this->imported++;
        }

        if ($this->imported === 0) {
            return;
        }

        // A plain delete, not truncate() — Excel::import() already wraps this
        // whole collection() call in a transaction (see config/excel.php's
        // transaction handler), and MySQL's TRUNCATE is an implicit commit
        // that breaks that surrounding transaction ("There is no active
        // transaction" on the next statement, despite the import succeeding).
        RegularPracticeAccess::query()->delete();
        foreach (array_chunk($batch, 500) as $chunk) {
            RegularPracticeAccess::insert($chunk);
        }
    }
}
