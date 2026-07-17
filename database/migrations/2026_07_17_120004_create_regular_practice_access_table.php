<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Level x Category x Type access grid for Regular Practice + Class Practice,
 * sourced from the client's "Regular Practice Sums Type" Excel — one row per
 * "YES" cell. category_id is deliberately not stored here; it's derived via
 * type.category_id to avoid denormalization drift.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regular_practice_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('regular_question_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['level_id', 'type_id']);
            $table->index('level_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regular_practice_access');
    }
};
