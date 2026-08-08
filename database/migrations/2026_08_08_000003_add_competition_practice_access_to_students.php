<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->boolean('competition_practice_access')->default(false)->after('is_active');
            $table->unsignedInteger('competition_practice_sessions_allowed')->default(0)->after('competition_practice_access');
        });

        // Backfill: students who already have Competition Practice attempts
        // are already using the feature — grant them the default 50-session
        // allowance so this new gate doesn't lock out anyone currently active.
        DB::statement(<<<'SQL'
            UPDATE students
            SET competition_practice_access = 1, competition_practice_sessions_allowed = 50
            WHERE id IN (SELECT DISTINCT student_id FROM competition_practice_attempts)
        SQL);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['competition_practice_access', 'competition_practice_sessions_allowed']);
        });
    }
};
