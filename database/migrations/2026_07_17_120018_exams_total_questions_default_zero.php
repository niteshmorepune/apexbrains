<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * total_questions is now derived from the uploaded Level-Up Exam paper's item
 * count, so Exam::create() must succeed before a paper exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_questions')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_questions')->change();
        });
    }
};
