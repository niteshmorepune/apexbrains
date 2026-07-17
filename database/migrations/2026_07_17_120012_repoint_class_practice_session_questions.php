<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class Practice draws exclusively from the Regular Question Bank per the
 * Class_Practice_Flow.docx flow.
 *
 * Drops whatever FK actually exists on the column rather than assuming
 * Laravel's default naming convention — see 2026_07_17_120011 for why.
 *
 * Does NOT re-add a strict FK to regular_question_banks: production has
 * ~700 pre-existing rows (June testing) whose question_id points to the old
 * question_banks ids, which don't exist in the new table. Same tradeoff as
 * 2026_07_17_120011 — confirmed low-value test data, not live customer data.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignKeysOn('class_practice_session_questions', 'question_id');
    }

    public function down(): void
    {
        $this->dropForeignKeysOn('class_practice_session_questions', 'question_id');

        Schema::table('class_practice_session_questions', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('question_banks')->cascadeOnDelete();
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
