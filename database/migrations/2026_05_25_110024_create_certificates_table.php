<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exam_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('competition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('certificate_number')->unique();
            $table->uuid('verification_code')->unique();
            $table->enum('type', ['level_completion', 'merit', 'excellence', 'competition']);
            $table->date('issued_at');
            $table->foreignId('issued_by')->constrained('users')->restrictOnDelete();
            $table->string('pdf_path')->nullable();
            $table->text('qr_data')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
