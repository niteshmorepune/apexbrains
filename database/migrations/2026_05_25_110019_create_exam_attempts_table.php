<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('franchise_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->json('question_ids');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('is_passed')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'auto_submitted', 'expired'])->default('in_progress');
            $table->unsignedSmallInteger('tab_switch_count')->default(0);
            $table->unsignedSmallInteger('fullscreen_exit_count')->default(0);
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
