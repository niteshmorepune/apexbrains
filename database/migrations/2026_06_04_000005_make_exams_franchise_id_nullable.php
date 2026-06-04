<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exams are now authored centrally by Admin and apply to all franchises, so
 * franchise_id becomes nullable (NULL = global exam). Existing per-franchise
 * exams keep their franchise_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['franchise_id']);
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('franchise_id')->nullable()->change();
            $table->foreign('franchise_id')->references('id')->on('franchises')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['franchise_id']);
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('franchise_id')->nullable(false)->change();
            $table->foreign('franchise_id')->references('id')->on('franchises')->cascadeOnDelete();
        });
    }
};
