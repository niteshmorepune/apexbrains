<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('question_category', ['level_practice', 'competition'])->default('level_practice');
            $table->unsignedSmallInteger('total_questions');
            $table->unsignedSmallInteger('time_per_question_seconds')->default(30);
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->unsignedSmallInteger('current_question_index')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('session_code', 6)->unique();
            $table->timestamps();

            $table->index(['franchise_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_practice_sessions');
    }
};
