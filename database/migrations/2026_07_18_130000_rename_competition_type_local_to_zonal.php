<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Client rename: the "Local" competition type is now labeled "Zonal"
 * (Zonal / Regional / National). Widen the enum first so existing 'local'
 * rows can be updated without MySQL's strict enum constraint rejecting the
 * value, backfill them, then narrow the enum to drop the old value.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE competitions MODIFY COLUMN competition_type ENUM('local','zonal','regional','national') NOT NULL");
        DB::table('competitions')->where('competition_type', 'local')->update(['competition_type' => 'zonal']);
        DB::statement("ALTER TABLE competitions MODIFY COLUMN competition_type ENUM('zonal','regional','national') NOT NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE competitions MODIFY COLUMN competition_type ENUM('local','zonal','regional','national') NOT NULL");
        DB::table('competitions')->where('competition_type', 'zonal')->update(['competition_type' => 'local']);
        DB::statement("ALTER TABLE competitions MODIFY COLUMN competition_type ENUM('local','regional','national') NOT NULL");
    }
};
