<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->foreignId('book_resource_id')
                ->nullable()
                ->after('learning_objectives')
                ->constrained('resource_files')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropForeign(['book_resource_id']);
            $table->dropColumn('book_resource_id');
        });
    }
};
