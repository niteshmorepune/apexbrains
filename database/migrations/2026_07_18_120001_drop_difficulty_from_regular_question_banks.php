<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Difficulty is no longer part of the Regular Practice Question Bank workflow
 * per client post-test feedback — dropped outright since nothing reads it
 * after this (see 2026_07_18_120002 for the matching Competition Bank drop).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regular_question_banks', function (Blueprint $table) {
            $table->dropColumn('difficulty');
        });
    }

    public function down(): void
    {
        Schema::table('regular_question_banks', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->after('correct_answer');
        });
    }
};
