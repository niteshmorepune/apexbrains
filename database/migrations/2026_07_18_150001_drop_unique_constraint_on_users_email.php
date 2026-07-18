<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MySQL's UNIQUE index has no concept of "unique among non-deleted rows" —
 * even with the app-level validation now scoped to whereNull('deleted_at')
 * (see Franchise\StudentController), the raw DB constraint still rejects a
 * new user row reusing a soft-deleted user's email, causing a 500 on
 * re-registration. Drop the hard constraint; uniqueness among active users
 * is enforced at the application layer instead. Keep a plain index so
 * login-by-email lookups stay fast.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->unique('email');
        });
    }
};
