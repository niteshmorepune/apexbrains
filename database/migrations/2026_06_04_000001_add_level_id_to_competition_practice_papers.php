<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_practice_papers', function (Blueprint $table) {
            $table->foreignId('level_id')->nullable()->after('description')->constrained()->nullOnDelete();
            $table->index('level_id');
        });
    }

    public function down(): void
    {
        Schema::table('competition_practice_papers', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropIndex(['level_id']);
            $table->dropColumn('level_id');
        });
    }
};
