<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Feeds Competition Practice only (Competition Exam papers are separate and
 * disposable — see competition_question_papers). MCQ-only by design: the
 * Competition Practice flow is "vertical sum display", never audio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('competition_question_categories')->restrictOnDelete();
            $table->foreignId('type_id')->constrained('competition_question_types')->restrictOnDelete();
            $table->text('question_text');
            $table->text('option_a')->nullable();
            $table->text('option_b')->nullable();
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->string('source_file')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_question_banks');
    }
};
