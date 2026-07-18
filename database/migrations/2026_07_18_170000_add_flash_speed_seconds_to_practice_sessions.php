<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Regular Practice now flashes the sum one digit at a time (the "1 Digit
 * Popup" method, matching Franchise Class Practice's existing flash-anzan
 * player) instead of showing the whole vertical sum at once. The student
 * picks the popup speed at session start, same allowed values as Class
 * Practice's time_per_question_seconds (3, 2.5, 2, 1.5, 1, 0.5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->float('flash_speed_seconds')->default(2)->after('total_questions');
        });
    }

    public function down(): void
    {
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->dropColumn('flash_speed_seconds');
        });
    }
};
