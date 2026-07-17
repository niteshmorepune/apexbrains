<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Level-Up Exam questions now come from level_up_exam_paper_items (uploaded
 * CSV papers) instead of the shared question_banks pool. exam_answers.question_id
 * keeps its name/shape (same fields as the item row) — no Blade changes needed.
 *
 * Drops whatever FK actually exists on the column rather than assuming
 * Laravel's default naming convention — production's constraint name didn't
 * match `exam_answers_question_id_foreign` (drift from an earlier deploy),
 * so a hardcoded dropForeign(['question_id']) failed with error 1091.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignKeysOn('exam_answers', 'question_id');

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('level_up_exam_paper_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $this->dropForeignKeysOn('exam_answers', 'question_id');

        Schema::table('exam_answers', function (Blueprint $table) {
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
