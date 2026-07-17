<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Level-Up Exam questions now come from level_up_exam_paper_items (uploaded
 * CSV papers) instead of the shared question_banks pool. exam_answers.question_id
 * keeps its name/shape (same fields as the item row) — no Blade changes needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('level_up_exam_paper_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('question_banks')->cascadeOnDelete();
        });
    }
};
