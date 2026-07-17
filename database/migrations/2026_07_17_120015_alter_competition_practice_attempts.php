<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Competition Practice no longer browses a pre-built paper — the full
 * question set is auto-generated per level at attempt-start and snapshotted
 * here, mirroring exam_attempts.question_ids.
 *
 * Drops whatever FK actually exists on the column rather than assuming
 * Laravel's default naming convention — see 2026_07_17_120011 for why.
 *
 * level_id/question_ids are added nullable (not the originally-planned
 * NOT NULL) because production has pre-existing rows (June testing) that
 * can't populate a NOT NULL column with no default. level_id is then
 * backfilled from the old paper's level while competition_practice_papers
 * still exists (it's dropped in the next migration) — real historical data
 * preserved, not just discarded. question_ids has no equivalent old data
 * (the old schema never snapshotted per-attempt questions), so it stays null
 * for historical rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_practice_attempts', function (Blueprint $table) {
            $table->foreignId('level_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->json('question_ids')->nullable()->after('level_id');
        });

        if (Schema::hasTable('competition_practice_papers')) {
            $fallbackLevelId = DB::table('levels')->orderBy('number')->value('id');

            DB::statement('
                UPDATE competition_practice_attempts cpa
                LEFT JOIN competition_practice_papers cpp ON cpp.id = cpa.paper_id
                SET cpa.level_id = COALESCE(cpp.level_id, ?)
            ', [$fallbackLevelId]);
        }

        $this->dropForeignKeysOn('competition_practice_attempts', 'paper_id');

        Schema::table('competition_practice_attempts', function (Blueprint $table) {
            $table->dropColumn('paper_id');
        });
    }

    public function down(): void
    {
        $this->dropForeignKeysOn('competition_practice_attempts', 'level_id');

        Schema::table('competition_practice_attempts', function (Blueprint $table) {
            $table->dropColumn(['level_id', 'question_ids']);
            $table->foreignId('paper_id')->after('id')->constrained('competition_practice_papers')->cascadeOnDelete();
        });
    }

    private function dropForeignKeysOn(string $table, string $column): void
    {
        $fks = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );

        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
    }
};
