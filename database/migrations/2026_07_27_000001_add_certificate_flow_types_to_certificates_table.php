<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen the type enum for the Certificate Generation flow (level_up,
        // participation, champion, winner) while keeping the legacy values so
        // existing production rows (level_completion, merit, excellence,
        // competition) keep rendering. MySQL-only ALTER — matches project convention.
        DB::statement("ALTER TABLE certificates MODIFY COLUMN type ENUM(
            'level_completion', 'merit', 'excellence', 'competition',
            'level_up', 'participation', 'champion', 'winner'
        ) NOT NULL");

        Schema::table('certificates', function (Blueprint $table) {
            // Rank within a level's competition results: 1-3 for champion, 4-6 for winner.
            $table->unsignedTinyInteger('rank')->nullable()->after('type');
            // Printed "Place" field (participation/level-up/champion/winner certificates).
            $table->string('place', 150)->nullable()->after('series');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['rank', 'place']);
        });

        DB::statement("ALTER TABLE certificates MODIFY COLUMN type ENUM(
            'level_completion', 'merit', 'excellence', 'competition'
        ) NOT NULL");
    }
};
