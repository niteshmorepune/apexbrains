<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-level Competition Practice countdown duration. The source Excel has no
 * duration column, so this is tracked separately from competition_practice_configs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_practice_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('duration_minutes')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_practice_levels');
    }
};
