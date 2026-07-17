<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Type is scoped under a category, not global — e.g. "1 Digit - 5 Rows"
 * exists as a distinct row under "Without Partners" and again under
 * "Grouping", each with its own id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regular_question_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('regular_question_categories')->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regular_question_types');
    }
};
