<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_code')->unique();
            $table->enum('student_type', ['internal', 'external'])->default('internal');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->date('enrollment_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('current_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['franchise_id', 'student_type', 'is_active']);
            $table->index('current_level_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
