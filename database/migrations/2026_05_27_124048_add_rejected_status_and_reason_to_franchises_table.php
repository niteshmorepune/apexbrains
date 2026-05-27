<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend enum to include 'rejected'
        DB::statement("ALTER TABLE franchises MODIFY COLUMN status ENUM('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending'");

        Schema::table('franchises', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('franchises', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        DB::statement("ALTER TABLE franchises MODIFY COLUMN status ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending'");
    }
};
