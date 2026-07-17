<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Level x Category x Type x Question-Count grid for Competition Practice,
 * sourced from the client's "Competition Practice Types" Excel. Competition
 * Practice generates its full question set automatically from these rows —
 * no manual category/type picking by the student.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_practice_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('competition_question_categories')->restrictOnDelete();
            $table->foreignId('type_id')->constrained('competition_question_types')->restrictOnDelete();
            $table->unsignedSmallInteger('question_count');
            // Kept for fidelity to the source Excel ("Marks / No Of Sums" column);
            // currently always == question_count (1 mark per question).
            $table->unsignedSmallInteger('marks');
            $table->timestamps();

            $table->unique(['level_id', 'category_id', 'type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_practice_configs');
    }
};
