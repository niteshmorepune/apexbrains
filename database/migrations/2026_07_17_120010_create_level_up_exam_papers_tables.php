<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Level-Up Exam question paper, uploaded by Admin via CSV per exam — mirrors
 * competition_question_papers/items exactly. `exams` stays the unchanged
 * parent config row (level, duration, pass_percentage, schedule, etc.);
 * this pair replaces the old dynamic draw from the shared Question Bank.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('level_up_exam_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['exam_id', 'is_active']);
        });

        Schema::create('level_up_exam_paper_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('level_up_exam_papers')->cascadeOnDelete();
            $table->text('question_text');
            $table->text('option_a')->nullable();
            $table->text('option_b')->nullable();
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_up_exam_paper_items');
        Schema::dropIfExists('level_up_exam_papers');
    }
};
