<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('level_resource_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_file_id')->constrained('resource_files')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['level_id', 'resource_file_id'], 'level_resource_unique');
        });

        // Migrate the existing single assigned book into the pivot
        foreach (DB::table('levels')->whereNotNull('book_resource_id')->get(['id', 'book_resource_id']) as $lvl) {
            DB::table('level_resource_files')->insert([
                'level_id'         => $lvl->id,
                'resource_file_id' => $lvl->book_resource_id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('level_resource_files');
    }
};
