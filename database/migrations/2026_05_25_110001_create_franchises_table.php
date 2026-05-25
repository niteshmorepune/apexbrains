<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('owner_name');
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->string('whatsapp', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('state')->default('Maharashtra');
            $table->string('gst_number', 20)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->string('franchise_code', 20)->unique();
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->decimal('fee_per_student', 8, 2)->default(1200.00);
            $table->string('logo')->nullable();
            $table->timestamp('agreed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Now add the FK from users to franchises
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('franchise_id')->references('id')->on('franchises')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['franchise_id']);
        });
        Schema::dropIfExists('franchises');
    }
};
