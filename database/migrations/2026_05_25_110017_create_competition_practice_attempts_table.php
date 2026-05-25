<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_practice_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('competition_practice_papers')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'auto_submitted'])->default('in_progress');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamps();

            $table->index(['paper_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_practice_attempts');
    }
};
