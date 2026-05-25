<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_practice_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('class_practice_sessions')->cascadeOnDelete();
            $table->foreignId('franchise_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('total_questions_shown');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_practice_results');
    }
};
