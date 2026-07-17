<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Competition Question Bank taxonomy (feeds Competition Practice only).
 * Sourced from the client's "Competition Practice Types" Excel. Kept fully
 * independent from the Regular taxonomy — see create_regular_question_categories.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_question_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_question_categories');
    }
};
