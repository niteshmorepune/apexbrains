<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The registration form's Monthly Fee field was auto-filled from the
 * assigned level's fee_per_month but was read-only and never actually
 * saved — every student's recurring fee silently came from the level's
 * current rate, with no way to set a custom/discounted rate per student.
 * Nullable: null means "use the level's rate" (existing students keep
 * that behavior automatically).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('monthly_fee', 8, 2)->nullable()->after('current_level_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('monthly_fee');
        });
    }
};
