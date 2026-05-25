<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('student_type', ['internal', 'external']);
            $table->decimal('amount', 8, 2);
            $table->date('month');
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending');
            $table->decimal('paid_amount', 8, 2)->default(0);
            $table->enum('fee_type', ['monthly', 'competition_registration'])->default('monthly');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
