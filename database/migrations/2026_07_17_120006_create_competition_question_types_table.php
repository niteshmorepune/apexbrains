<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_question_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('competition_question_categories')->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_question_types');
    }
};
