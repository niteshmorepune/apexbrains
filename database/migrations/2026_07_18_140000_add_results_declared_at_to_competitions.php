<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gates the score/correct-wrong/rank/certificate detail on a competition's
 * result page — students only see full results once an admin explicitly
 * declares them. Nullable = not yet declared; set once, on demand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->timestamp('results_declared_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('results_declared_at');
        });
    }
};
