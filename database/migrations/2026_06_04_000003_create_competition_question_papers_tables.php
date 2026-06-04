<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Level-wise question papers for an actual (scheduled) competition. Uploaded by
 * Admin via CSV at competition time and deleted once the competition is over.
 * Kept separate from the shared Question Bank so competition content stays
 * private and disposable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_question_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('duration_minutes')->default(10);
            $table->unsignedTinyInteger('pass_percentage')->default(75);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['competition_id', 'level_id']);
        });

        Schema::create('competition_question_paper_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('competition_question_papers')->cascadeOnDelete();
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
        Schema::dropIfExists('competition_question_paper_items');
        Schema::dropIfExists('competition_question_papers');
    }
};
