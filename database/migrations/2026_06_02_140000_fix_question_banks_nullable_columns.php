<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Align question_banks columns with how the app actually uses them:
     * - correct_answer: audio questions have no answer, so it must be nullable.
     * - question_category: used everywhere as an optional free-text tag, not the
     *   original enum, so widen it to a nullable string.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE question_banks MODIFY correct_answer ENUM('a','b','c','d') NULL");
        DB::statement("ALTER TABLE question_banks MODIFY question_category VARCHAR(100) NULL");
    }

    public function down(): void
    {
        // Restore is best-effort; rows with NULLs would need cleanup first.
        DB::statement("ALTER TABLE question_banks MODIFY correct_answer ENUM('a','b','c','d') NOT NULL");
        DB::statement("ALTER TABLE question_banks MODIFY question_category ENUM('level_practice','competition','class_practice') NOT NULL");
    }
};
