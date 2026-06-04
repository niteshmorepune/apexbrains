<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('competition_question_papers')->cascadeOnDelete();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedSmallInteger('score')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->enum('status', ['in_progress', 'submitted'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            // Exam integrity (CLAUDE.md rule 8)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedSmallInteger('tab_switch_count')->default(0);
            $table->timestamps();
            $table->index(['competition_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_exam_attempts');
    }
};
