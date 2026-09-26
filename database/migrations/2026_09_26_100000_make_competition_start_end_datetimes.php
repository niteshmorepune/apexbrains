<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Competition start/end become exact date + time (the exam window), stored as
 * true UTC instants like exams.scheduled_at — admin enters IST, app is UTC.
 *
 * Existing rows only had a calendar date and were treated as open for the
 * whole IST day(s), so they are converted to exactly that window:
 * start → 00:00:00 IST on start_date, end → 23:59:59 IST on end_date.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dateTime('start_date')->change();
            $table->dateTime('end_date')->change();
        });

        if (DB::getDriverName() === 'mysql') {
            // Values are now 'Y-m-d 00:00:00' (the IST calendar date).
            DB::statement("UPDATE competitions SET
                start_date = DATE_SUB(start_date, INTERVAL '05:30' HOUR_MINUTE),
                end_date   = DATE_ADD(end_date, INTERVAL '18:29:59' HOUR_SECOND)");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE competitions SET
                start_date = DATE_ADD(start_date, INTERVAL '05:30' HOUR_MINUTE),
                end_date   = DATE_ADD(end_date, INTERVAL '05:30' HOUR_MINUTE)");
        }

        Schema::table('competitions', function (Blueprint $table) {
            $table->date('start_date')->change();
            $table->date('end_date')->change();
        });
    }
};
