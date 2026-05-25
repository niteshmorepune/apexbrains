<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_paper_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('competition_practice_papers')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_banks')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_paper_questions');
    }
};
