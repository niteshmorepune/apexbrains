<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The pre-built "Practice Papers" catalogue (Franchise Class Practice +
 * Student Competition Practice) is removed in favor of on-demand generation
 * per the client's flow docs. Must run after competition_practice_attempts
 * has been repointed off paper_id (see 2026_07_17_120015).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('competition_paper_questions');
        Schema::dropIfExists('competition_practice_papers');
    }

    public function down(): void
    {
        Schema::create('competition_practice_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('total_questions')->default(50);
            $table->unsignedSmallInteger('duration_minutes')->default(10);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('paper_number');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('competition_paper_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('competition_practice_papers')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_banks')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
        });
    }
};
