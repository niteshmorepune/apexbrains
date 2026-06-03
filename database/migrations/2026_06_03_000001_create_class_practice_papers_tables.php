<?php

use Database\Seeders\ClassPracticePapersSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_practice_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('paper_number');
            $table->unsignedSmallInteger('total_questions')->default(20);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('easy');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['level_id', 'paper_number']);
        });

        Schema::create('class_practice_paper_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('class_practice_papers')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('question_banks')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
        });

        // Populate on environments that already have an approved question bank
        // (e.g. production, where the deploy runs `migrate` but not seeders).
        // Idempotent + no-ops when the question bank is still empty, so fresh
        // installs are populated later by DatabaseSeeder instead.
        (new ClassPracticePapersSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('class_practice_paper_questions');
        Schema::dropIfExists('class_practice_papers');
    }
};
