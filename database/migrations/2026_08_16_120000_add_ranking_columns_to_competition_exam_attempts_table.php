<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_exam_attempts', function (Blueprint $table) {
            $table->unsignedSmallInteger('questions_attempted')->nullable()->after('score');
            $table->unsignedSmallInteger('wrong_answers')->nullable()->after('questions_attempted');
            $table->unsignedSmallInteger('rank')->nullable()->after('percentage');
        });
    }

    public function down(): void
    {
        Schema::table('competition_exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['questions_attempted', 'wrong_answers', 'rank']);
        });
    }
};
