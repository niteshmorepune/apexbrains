<?php

namespace Tests\Support;

use App\Models\Fee;
use App\Models\Franchise;
use App\Models\Level;
use App\Models\Student;
use App\Models\User;

trait CreatesFranchise
{
    private int $seqCounter = 0;

    private function seq(): int
    {
        return ++$this->seqCounter;
    }

    protected function makeFranchise(string $name = 'Test Franchise'): Franchise
    {
        $n = $this->seq();
        return Franchise::create([
            'name'           => $name,
            'slug'           => 'franchise-' . $n,
            'owner_name'     => 'Owner',
            'email'          => 'franchise' . $n . '@test.com',
            'phone'          => '990000' . str_pad($n, 4, '0', STR_PAD_LEFT),
            'status'         => 'active',
            'franchise_code' => 'FC' . str_pad($n, 3, '0', STR_PAD_LEFT),
        ]);
    }

    protected function makeFranchiseAdmin(Franchise $franchise): User
    {
        $admin = User::factory()->create(['franchise_id' => $franchise->id]);
        $admin->assignRole('franchise_admin');
        return $admin;
    }

    protected function makeLevel(int $number = 1, string $title = 'Level 1'): Level
    {
        $n = $this->seq();
        return Level::create([
            'number'   => $number,
            'title'    => $title,
            'slug'     => 'level-' . $number . '-' . $n,
            'is_active'=> true,
        ]);
    }

    protected function makeStudent(Franchise $franchise, User $user, string $code = null): Student
    {
        $n = $this->seq();
        return Student::create([
            'franchise_id'   => $franchise->id,
            'user_id'        => $user->id,
            'student_code'   => $code ?? 'STU-' . $n,
            'student_type'   => 'internal',
            'first_name'     => 'Student',
            'last_name'      => $n,
            'date_of_birth'  => '2010-01-01',
            'gender'         => 'male',
            'enrollment_date'=> now()->toDateString(),
        ]);
    }

    protected function makeStudentWithUser(Franchise $franchise): array
    {
        $user = User::factory()->create([
            'franchise_id' => $franchise->id,
            'student_type' => 'internal',
            'is_active'    => true,
        ]);
        $user->assignRole('student');
        $student = $this->makeStudent($franchise, $user);
        return [$user, $student];
    }

    protected function makeFee(Franchise $franchise, Student $student, float $amount = 1000, string $status = 'due'): Fee
    {
        return Fee::withoutGlobalScopes()->create([
            'franchise_id' => $franchise->id,
            'student_id'   => $student->id,
            'student_type' => $student->student_type,
            'amount'       => $amount,
            'paid_amount'  => 0,
            'month'        => now()->startOfMonth()->toDateString(),
            'due_date'     => now()->toDateString(),
            'status'       => $status === 'due' ? 'pending' : $status,
            'fee_type'     => 'monthly',
        ]);
    }
}
