<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the "Session Length" field from Class Practice (client decision, 2026-06).
 * The per-flash timer (time_per_question_seconds) is the only timing control now.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_practice_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('class_practice_sessions', 'session_length_minutes')) {
                $table->dropColumn('session_length_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('class_practice_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('class_practice_sessions', 'session_length_minutes')) {
                $table->unsignedSmallInteger('session_length_minutes')->nullable()->after('time_per_question_seconds');
            }
        });
    }
};
