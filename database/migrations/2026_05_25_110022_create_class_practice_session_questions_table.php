<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_practice_session_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('class_practice_sessions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_banks')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_practice_session_questions');
    }
};
