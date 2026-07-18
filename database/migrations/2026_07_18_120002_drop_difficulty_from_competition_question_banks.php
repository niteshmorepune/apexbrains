<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Difficulty is no longer part of the Competition Practice Question Bank
 * workflow per client post-test feedback — dropped outright since nothing
 * reads it after this (External\PracticeController no longer filters by it).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_question_banks', function (Blueprint $table) {
            $table->dropColumn('difficulty');
        });
    }

    public function down(): void
    {
        Schema::table('competition_question_banks', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->after('correct_answer');
        });
    }
};
