<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Competition registration fees exist in `fees` (fee_type =
 * 'competition_registration') but had no FK back to WHICH competition
 * registration they belong to — student_id + fee_type alone can't
 * disambiguate once a student has more than one competition registration.
 * Also backfills a Fee row for every existing pending registration that
 * never got one (the "Register a student" flow never created one at all),
 * so they become payable through the existing Record Payment screen
 * instead of being permanently stuck on "Payment Pending".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->foreignId('competition_registration_id')->nullable()
                ->after('level_id')->constrained()->nullOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        $now = now();

        $pending = DB::table('competition_registrations')
            ->where('payment_status', 'pending')
            ->get(['id', 'competition_id', 'student_id', 'franchise_id', 'student_type', 'registration_date']);

        foreach ($pending as $reg) {
            $competition = DB::table('competitions')->where('id', $reg->competition_id)->first(['fee_amount']);
            $feeAmount = (float) ($competition->fee_amount ?? 0);

            if ($feeAmount <= 0) {
                // Nothing owed — a free competition was never really "pending".
                DB::table('competition_registrations')->where('id', $reg->id)
                    ->update(['payment_status' => 'paid']);
                continue;
            }

            $alreadyHasFee = DB::table('fees')->where('competition_registration_id', $reg->id)->exists();
            if ($alreadyHasFee) {
                continue;
            }

            DB::table('fees')->insert([
                'franchise_id' => $reg->franchise_id,
                'student_id' => $reg->student_id,
                'level_id' => null,
                'competition_registration_id' => $reg->id,
                'student_type' => $reg->student_type,
                'amount' => $feeAmount,
                'month' => $now->copy()->startOfMonth()->toDateString(),
                'due_date' => $now->copy()->addDays(7)->toDateString(),
                'status' => 'pending',
                'paid_amount' => 0,
                'fee_type' => 'competition_registration',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_registration_id');
        });
    }
};
