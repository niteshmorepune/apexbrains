<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class Practice now picks a category+type instead of drawing blind from the
 * level pool. The old `question_category` enum (always hardcoded to
 * 'level_practice') is dropped — no remaining purpose once the Practice
 * Papers catalogue is removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_practice_sessions', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('level_id')->constrained('regular_question_categories')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->after('category_id')->constrained('regular_question_types')->nullOnDelete();
            $table->dropColumn('question_category');
        });
    }

    public function down(): void
    {
        Schema::table('class_practice_sessions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['type_id']);
            $table->dropColumn(['category_id', 'type_id']);
            $table->enum('question_category', ['level_practice', 'competition'])->default('level_practice');
        });
    }
};
