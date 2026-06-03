<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('series', 50)->nullable()->after('type');
            $table->timestamp('sent_at')->nullable()->after('issued_at');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['series', 'sent_at']);
        });
    }
};
