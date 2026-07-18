<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same issue as users.email (see 2026_07_18_150001): franchises already
 * supports soft-delete, but MySQL's UNIQUE index has no concept of "unique
 * among non-deleted rows" — re-registering a franchise with a deleted
 * franchise's email would 500 on the raw constraint even with the
 * application-level check now scoped to whereNull('deleted_at'). Drop the
 * hard constraint; uniqueness among active franchises is enforced at the
 * application layer instead, with a plain index kept for lookup speed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('franchises', function (Blueprint $table) {
            $table->dropUnique('franchises_email_unique');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('franchises', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->unique('email');
        });
    }
};
