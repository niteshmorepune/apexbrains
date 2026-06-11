<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a sequential 2-digit franchise_number used in the 8-digit student ID
 * (YY + FF + SSSS), approved in the 2026-06 client meeting. Existing franchises
 * are backfilled in id order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('franchises', function (Blueprint $table) {
            $table->unsignedTinyInteger('franchise_number')->nullable()->unique()->after('franchise_code');
        });

        // Backfill sequentially by id so existing franchises get 1, 2, 3, …
        $seq = 0;
        foreach (DB::table('franchises')->orderBy('id')->pluck('id') as $id) {
            DB::table('franchises')->where('id', $id)->update(['franchise_number' => ++$seq]);
        }
    }

    public function down(): void
    {
        Schema::table('franchises', function (Blueprint $table) {
            $table->dropColumn('franchise_number');
        });
    }
};
