<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Competition Practice no longer browses a pre-built paper — the full
 * question set is auto-generated per level at attempt-start and snapshotted
 * here, mirroring exam_attempts.question_ids.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_practice_attempts', function (Blueprint $table) {
            $table->dropForeign(['paper_id']);
            $table->dropColumn('paper_id');
            $table->foreignId('level_id')->after('id')->constrained()->restrictOnDelete();
            $table->json('question_ids')->after('level_id');
        });
    }

    public function down(): void
    {
        Schema::table('competition_practice_attempts', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropColumn(['level_id', 'question_ids']);
            $table->foreignId('paper_id')->after('id')->constrained('competition_practice_papers')->cascadeOnDelete();
        });
    }
};
