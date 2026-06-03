<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remap any legacy status values to the set the controller/views actually use
        // (the original enum was draft/active/paused/completed but the app uses pending/active/ended).
        DB::table('class_practice_sessions')->where('status', 'draft')->update(['status' => 'active']);
        DB::table('class_practice_sessions')->where('status', 'paused')->update(['status' => 'active']);
        DB::table('class_practice_sessions')->where('status', 'completed')->update(['status' => 'active']);

        // Align the status enum with pending/active/ended (used throughout ClassPracticeController + views).
        DB::statement("ALTER TABLE class_practice_sessions MODIFY status ENUM('pending','active','ended') NOT NULL DEFAULT 'pending'");

        Schema::table('class_practice_sessions', function (Blueprint $table) {
            // Time per step is shown in Figma as 2 / 2.5 / 3 seconds — needs decimal support.
            $table->decimal('time_per_question_seconds', 4, 1)->default(2.0)->change();

            // Figma setup form options that previously had nowhere to be stored.
            $table->unsignedSmallInteger('session_length_minutes')->nullable()->after('time_per_question_seconds');
            $table->boolean('audio_dictation')->default(true)->after('session_length_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('class_practice_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_length_minutes', 'audio_dictation']);
            $table->unsignedSmallInteger('time_per_question_seconds')->default(30)->change();
        });

        DB::statement("ALTER TABLE class_practice_sessions MODIFY status ENUM('draft','active','paused','completed') NOT NULL DEFAULT 'draft'");
    }
};
