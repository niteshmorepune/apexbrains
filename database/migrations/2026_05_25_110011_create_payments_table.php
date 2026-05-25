<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_id')->constrained()->restrictOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 8, 2);
            $table->enum('payment_mode', ['cash', 'upi', 'card', 'cheque', 'bank_transfer']);
            $table->string('transaction_reference')->nullable();
            $table->date('payment_date');
            $table->string('receipt_pdf_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
