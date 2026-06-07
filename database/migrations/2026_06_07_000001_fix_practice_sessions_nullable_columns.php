<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The student self-practice flow only populates the result columns
     * (questions_correct, accuracy, avg_speed_seconds, completed_at) when a
     * session is finalized — they are NULL while a session is in progress.
     * The original migration made them NOT NULL with no default, so under
     * STRICT_TRANS_TABLES the initial PracticeSession::create() always failed.
     *
     * The difficulty enum was also out of step: the form/controller use
     * easy/medium/hard (matching question_banks) but the column was
     * beginner/intermediate/advanced. The redesigned form no longer asks for
     * difficulty at all, so it is now nullable too.
     */
    public function up(): void
    {
        // SQLite (used only by the test suite) doesn't enforce enum/NOT NULL the
        // way MySQL does and doesn't support ALTER ... MODIFY, so there is nothing
        // to fix there — the fresh test schema is harmless as-is.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable()->change();
            $table->unsignedSmallInteger('questions_correct')->nullable()->change();
            $table->decimal('accuracy', 5, 2)->nullable()->change();
            $table->decimal('avg_speed_seconds', 5, 2)->nullable()->change();
            $table->unsignedSmallInteger('duration_minutes')->nullable()->change();
            $table->timestamp('completed_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->nullable(false)->change();
            $table->unsignedSmallInteger('questions_correct')->nullable(false)->change();
            $table->decimal('accuracy', 5, 2)->nullable(false)->change();
            $table->decimal('avg_speed_seconds', 5, 2)->nullable(false)->change();
            $table->unsignedSmallInteger('duration_minutes')->nullable(false)->change();
            $table->timestamp('completed_at')->nullable(false)->change();
        });
    }
};
