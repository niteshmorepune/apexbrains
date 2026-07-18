<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a Student only soft-deleted the students row — the linked users
 * row (and its unique email) stayed live, so re-registering with the same
 * email failed the unique:users,email check. Users now support soft-delete
 * too, so Student's delete event (see Student::booted()) can soft-delete
 * the linked user and free the email via a deleted_at-aware unique rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
