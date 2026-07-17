<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Regular Practice now picks a category+type instead of a difficulty.
 * `difficulty` is left in place (nullable, unused by the new flow) rather
 * than dropped — harmless optional metadata, avoids an extra destructive change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('level_id')->constrained('regular_question_categories')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->after('category_id')->constrained('regular_question_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('practice_sessions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['type_id']);
            $table->dropColumn(['category_id', 'type_id']);
        });
    }
};
