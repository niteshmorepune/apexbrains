<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class Practice draws exclusively from the Regular Question Bank per the
 * Class_Practice_Flow.docx flow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_practice_session_questions', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });

        Schema::table('class_practice_session_questions', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('regular_question_banks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_practice_session_questions', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });

        Schema::table('class_practice_session_questions', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('question_banks')->cascadeOnDelete();
        });
    }
};
