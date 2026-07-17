<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Regular Question Bank taxonomy (feeds Regular Practice + Class Practice).
 * Sourced from the client's "Regular Practice Sums Type" Excel. Independent
 * from the Competition taxonomy (see create_competition_question_categories) —
 * the two source spreadsheets use different spellings/type sets, so keeping
 * them in separate tables avoids cross-contamination.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regular_question_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regular_question_categories');
    }
};
