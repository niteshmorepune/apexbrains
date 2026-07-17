<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the old shared `question_banks` table for Regular Practice and
 * Class Practice. Questions are tied to a (category, type) pair, never to a
 * Level directly — Levels only gain access via `regular_practice_access`.
 *
 * `answer_format` is a deliberate rename of the old `type` column (which
 * meant "mcq vs audio") to avoid clashing with the new taxonomy's "type"
 * (the abacus operation subtype, e.g. "1 Digit - 5 Rows").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regular_question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('regular_question_categories')->restrictOnDelete();
            $table->foreignId('type_id')->constrained('regular_question_types')->restrictOnDelete();
            $table->text('question_text');
            $table->enum('answer_format', ['mcq', 'audio'])->default('mcq');
            $table->text('option_a')->nullable();
            $table->text('option_b')->nullable();
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            $table->enum('correct_answer', ['a', 'b', 'c', 'd'])->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->string('audio_file_path')->nullable();
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
        Schema::dropIfExists('regular_question_banks');
    }
};
