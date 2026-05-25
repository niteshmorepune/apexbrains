<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_practice_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('total_questions')->default(50);
            $table->unsignedSmallInteger('duration_minutes')->default(10);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('paper_number'); // 1–50
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_practice_papers');
    }
};
