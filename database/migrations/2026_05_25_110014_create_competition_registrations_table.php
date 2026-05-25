<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('franchise_id')->constrained()->restrictOnDelete();
            $table->enum('student_type', ['internal', 'external']);
            $table->date('registration_date');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['registered', 'confirmed', 'disqualified'])->default('registered');
            $table->timestamps();

            $table->unique(['competition_id', 'student_id']);
            $table->index(['competition_id', 'student_id', 'student_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_registrations');
    }
};
