<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->restrictOnDelete();
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced']);
            $table->unsignedSmallInteger('total_questions');
            $table->unsignedSmallInteger('questions_correct');
            $table->decimal('accuracy', 5, 2);
            $table->decimal('avg_speed_seconds', 5, 2);
            $table->unsignedSmallInteger('duration_minutes');
            $table->timestamp('completed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};
