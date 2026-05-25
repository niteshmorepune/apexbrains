<?php

namespace Database\Seeders;

use App\Models\Franchise;
use App\Models\Level;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $admin = User::firstOrCreate(['email' => 'admin@apexbrains.in'], [
            'name'       => 'Apex Admin',
            'password'   => Hash::make('password'),
            'is_active'  => true,
        ]);
        $admin->assignRole('super_admin');

        // 2. Demo Franchise
        $franchise = Franchise::firstOrCreate(['slug' => 'kothrud'], [
            'name'            => 'Apex Brains Kothrud',
            'owner_name'      => 'Ravi Sharma',
            'email'           => 'kothrud@apexbrains.in',
            'phone'           => '9876543210',
            'address'         => 'Kothrud, Pune',
            'city'            => 'Pune',
            'pincode'         => '411038',
            'state'           => 'Maharashtra',
            'status'          => 'active',
            'franchise_code'  => 'KTH001',
            'commission_rate' => 10.00,
            'fee_per_student' => 1200.00,
            'agreed_at'       => now(),
        ]);

        // 3. Franchise Admin User
        $franchiseUser = User::firstOrCreate(['email' => 'kothrud@apexbrains.in'], [
            'name'         => 'Ravi Sharma',
            'password'     => Hash::make('password'),
            'franchise_id' => $franchise->id,
            'phone'        => '9876543210',
            'is_active'    => true,
        ]);
        $franchiseUser->assignRole('franchise_admin');

        // 4. Internal Student User
        $internalUser = User::firstOrCreate(['email' => 'arjun@student.in'], [
            'name'         => 'Arjun Patil',
            'password'     => Hash::make('password'),
            'franchise_id' => $franchise->id,
            'student_type' => 'internal',
            'is_active'    => true,
        ]);
        $internalUser->assignRole('student');

        $level1 = Level::where('number', 1)->first();
        if ($level1) {
            Student::firstOrCreate(['user_id' => $internalUser->id], [
                'franchise_id'      => $franchise->id,
                'student_code'      => 'KTH-INT-001',
                'student_type'      => 'internal',
                'first_name'        => 'Arjun',
                'last_name'         => 'Patil',
                'date_of_birth'     => '2015-06-15',
                'gender'            => 'male',
                'enrollment_date'   => now()->toDateString(),
                'current_level_id'  => $level1->id,
                'is_active'         => true,
            ]);
        }

        // 5. External Student User
        $externalUser = User::firstOrCreate(['email' => 'external@test.in'], [
            'name'         => 'Priya Mehta',
            'password'     => Hash::make('password'),
            'franchise_id' => $franchise->id,
            'student_type' => 'external',
            'is_active'    => true,
        ]);
        $externalUser->assignRole('student');

        Student::firstOrCreate(['user_id' => $externalUser->id], [
            'franchise_id'     => $franchise->id,
            'student_code'     => 'KTH-EXT-001',
            'student_type'     => 'external',
            'first_name'       => 'Priya',
            'last_name'        => 'Mehta',
            'date_of_birth'    => '2014-03-20',
            'gender'           => 'female',
            'enrollment_date'  => now()->toDateString(),
            'current_level_id' => null, // external has no level
            'is_active'        => true,
        ]);
    }
}
