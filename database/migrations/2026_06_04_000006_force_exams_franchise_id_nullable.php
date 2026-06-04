<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deterministically make exams.franchise_id nullable via raw SQL.
 *
 * The earlier Blueprint ->change() migration (…000005) can silently no-op on
 * MariaDB (Hostinger), leaving the column NOT NULL even though the migration is
 * marked as run — which 500s when Admin creates a global exam (franchise_id =
 * NULL). Raw ALTER behaves identically on MySQL and MariaDB. Idempotent and a
 * harmless no-op where the column is already nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK so the column can be modified on all engines, then re-add it.
        try {
            Schema::table('exams', fn (Blueprint $t) => $t->dropForeign(['franchise_id']));
        } catch (\Throwable $e) {
            // FK may already be absent — ignore.
        }

        DB::statement('ALTER TABLE `exams` MODIFY `franchise_id` BIGINT UNSIGNED NULL');

        try {
            Schema::table('exams', function (Blueprint $t) {
                $t->foreign('franchise_id')->references('id')->on('franchises')->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // FK already present — ignore.
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: reverting to NOT NULL would break global exams.
    }
};
