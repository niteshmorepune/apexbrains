<?php

namespace App\Services;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Support\Carbon;

/**
 * Generates recurring monthly tuition fees for internal students without any
 * server cron (Hostinger-friendly):
 *  - ensureFirstFee()  — creates the enrollment-month fee when a student joins.
 *  - createNextMonthFee() — when a monthly fee is fully paid, seeds the next
 *    month's pending fee so collection rolls forward automatically.
 *
 * External (competition-only) students are excluded — they pay per-competition
 * registration fees, not monthly tuition.
 */
class MonthlyFeeService
{
    /**
     * Create the first month's tuition fee for a newly registered internal student.
     */
    public function ensureFirstFee(Student $student): ?Fee
    {
        if ($student->student_type !== 'internal') {
            return null;
        }

        $enrolledAt = $student->enrollment_date
            ? Carbon::parse($student->enrollment_date)
            : now();

        $month = $enrolledAt->copy()->startOfMonth()->toDateString();

        return $this->createFeeForMonth($student, $month, $enrolledAt->toDateString());
    }

    /**
     * After a monthly fee is fully paid, create the following month's pending fee.
     */
    public function createNextMonthFee(Fee $fee): ?Fee
    {
        if ($fee->fee_type !== 'monthly') {
            return null;
        }

        $student = $fee->student;
        if (! $student || $student->student_type !== 'internal' || ! $student->is_active) {
            return null;
        }

        $nextMonth = Carbon::parse($fee->month)->copy()->addMonthNoOverflow()->startOfMonth();
        $nextDue   = Carbon::parse($fee->due_date)->copy()->addMonthNoOverflow();

        return $this->createFeeForMonth($student, $nextMonth->toDateString(), $nextDue->toDateString());
    }

    /**
     * Idempotently create a monthly fee for the given month (no duplicate per
     * student + month). Amount comes from the student's current level.
     */
    private function createFeeForMonth(Student $student, string $month, string $dueDate): ?Fee
    {
        $exists = Fee::where('student_id', $student->id)
            ->where('fee_type', 'monthly')
            ->whereDate('month', $month)
            ->exists();

        if ($exists) {
            return null;
        }

        $amount = (float) ($student->currentLevel->fee_per_month ?? 0);

        return Fee::create([
            'franchise_id' => $student->franchise_id,
            'student_id'   => $student->id,
            'level_id'     => $student->current_level_id,
            'student_type' => 'internal',
            'amount'       => $amount,
            'month'        => $month,
            'due_date'     => $dueDate,
            'status'       => 'pending',
            'paid_amount'  => 0,
            'fee_type'     => 'monthly',
        ]);
    }
}
