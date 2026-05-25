<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question_text');
            $table->enum('type', ['mcq', 'audio'])->default('mcq');
            $table->text('option_a')->nullable();
            $table->text('option_b')->nullable();
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('audio_file_path')->nullable();
            $table->enum('question_category', ['level_practice', 'competition', 'class_practice']);
            $table->string('source_pdf')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['level_id', 'question_category', 'status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
