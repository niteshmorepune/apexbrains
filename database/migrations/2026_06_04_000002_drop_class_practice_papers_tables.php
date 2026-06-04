<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire the separate class_practice_papers catalogue. The Franchise Class
 * Practice module now reads the unified, Admin-authored Practice Papers
 * (competition_practice_papers), so these tables are no longer used.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('class_practice_paper_questions');
        Schema::dropIfExists('class_practice_papers');
    }

    public function down(): void
    {
        Schema::create('class_practice_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('paper_number');
            $table->unsignedSmallInteger('total_questions')->default(20);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['level_id', 'paper_number']);
        });

        Schema::create('class_practice_paper_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('class_practice_papers')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_banks')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
        });
    }
};
