<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('franchise_id')->nullable()->constrained()->nullOnDelete(); // null = global
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('competition_type', ['local', 'regional', 'national']);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('registration_deadline');
            $table->unsignedInteger('max_participants')->nullable();
            $table->decimal('fee_amount', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_open_to_external')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
