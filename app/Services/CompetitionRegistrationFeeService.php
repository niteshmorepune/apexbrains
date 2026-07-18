<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\Fee;

/**
 * Creates the payable Fee record for a competition registration so it can
 * be settled through the existing Record Payment screen instead of being
 * permanently stuck on "Payment Pending" with nothing to pay. A free
 * competition (fee_amount = 0) has nothing owed, so the registration is
 * marked paid immediately instead of creating a zero-amount fee.
 */
class CompetitionRegistrationFeeService
{
    public function createFeeFor(CompetitionRegistration $registration, Competition $competition): ?Fee
    {
        $feeAmount = (float) $competition->fee_amount;

        if ($feeAmount <= 0) {
            $registration->update(['payment_status' => 'paid']);

            return null;
        }

        return Fee::create([
            'franchise_id' => $registration->franchise_id,
            'student_id' => $registration->student_id,
            'level_id' => null,
            'competition_registration_id' => $registration->id,
            'student_type' => $registration->student_type,
            'amount' => $feeAmount,
            'month' => now()->startOfMonth()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'pending',
            'paid_amount' => 0,
            'fee_type' => 'competition_registration',
        ]);
    }
}
