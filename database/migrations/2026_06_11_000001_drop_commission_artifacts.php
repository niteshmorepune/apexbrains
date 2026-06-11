<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the Commission Calculator feature (client decision, 2026-06).
 * Drops the commissions table and the franchise-level commission columns.
 * Revenue is now derived solely from collected payments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('commissions');

        Schema::table('franchises', function (Blueprint $table) {
            if (Schema::hasColumn('franchises', 'commission_rate')) {
                $table->dropColumn('commission_rate');
            }
            if (Schema::hasColumn('franchises', 'fee_per_student')) {
                $table->dropColumn('fee_per_student');
            }
        });
    }

    public function down(): void
    {
        Schema::table('franchises', function (Blueprint $table) {
            if (! Schema::hasColumn('franchises', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(10.00);
            }
            if (! Schema::hasColumn('franchises', 'fee_per_student')) {
                $table->decimal('fee_per_student', 8, 2)->default(1200.00);
            }
        });

        // Recreate the commissions table to match the original schema.
        if (! Schema::hasTable('commissions')) {
            Schema::create('commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('franchise_id')->constrained()->cascadeOnDelete();
                $table->date('month');
                $table->unsignedInteger('students_count')->default(0);
                $table->decimal('fee_per_student', 8, 2)->default(0);
                $table->decimal('gross_revenue', 12, 2)->default(0);
                $table->decimal('commission_rate', 5, 2)->default(0);
                $table->decimal('commission_amount', 12, 2)->default(0);
                $table->enum('status', ['pending', 'paid'])->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['franchise_id', 'month']);
            });
        }
    }
};
