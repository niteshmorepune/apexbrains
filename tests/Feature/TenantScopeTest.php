<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Fee;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesFranchise;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase, CreatesFranchise;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_franchise_admin_only_sees_own_students(): void
    {
        $franchiseA = $this->makeFranchise('Franchise A');
        $franchiseB = $this->makeFranchise('Franchise B');
        $adminA     = $this->makeFranchiseAdmin($franchiseA);
        $this->makeFranchiseAdmin($franchiseB);

        [$userA] = $this->makeStudentWithUser($franchiseA);
        [$userB] = $this->makeStudentWithUser($franchiseB);

        $this->actingAs($adminA);
        $students = Student::all();

        $this->assertCount(1, $students);
        $this->assertEquals($franchiseA->id, $students->first()->franchise_id);
    }

    public function test_franchise_admin_cannot_see_other_franchise_exams(): void
    {
        $franchiseA = $this->makeFranchise('Franchise A');
        $franchiseB = $this->makeFranchise('Franchise B');
        $adminA     = $this->makeFranchiseAdmin($franchiseA);
        $adminB     = $this->makeFranchiseAdmin($franchiseB);
        $level      = $this->makeLevel();

        Exam::withoutGlobalScopes()->create([
            'franchise_id' => $franchiseA->id, 'level_id' => $level->id,
            'title' => 'Exam A', 'total_questions' => 5,
            'duration_minutes' => 30, 'pass_percentage' => 60,
            'is_active' => true, 'created_by' => $adminA->id,
        ]);
        Exam::withoutGlobalScopes()->create([
            'franchise_id' => $franchiseB->id, 'level_id' => $level->id,
            'title' => 'Exam B', 'total_questions' => 5,
            'duration_minutes' => 30, 'pass_percentage' => 60,
            'is_active' => true, 'created_by' => $adminB->id,
        ]);

        $this->actingAs($adminA);
        $exams = Exam::all();

        $this->assertCount(1, $exams);
        $this->assertEquals('Exam A', $exams->first()->title);
    }

    public function test_franchise_admin_cannot_see_other_franchise_fees(): void
    {
        $franchiseA = $this->makeFranchise('Franchise A');
        $franchiseB = $this->makeFranchise('Franchise B');
        $adminA     = $this->makeFranchiseAdmin($franchiseA);
        $this->makeFranchiseAdmin($franchiseB);

        [, $studentA] = $this->makeStudentWithUser($franchiseA);
        [, $studentB] = $this->makeStudentWithUser($franchiseB);

        $this->makeFee($franchiseA, $studentA, 500);
        $this->makeFee($franchiseB, $studentB, 600);

        $this->actingAs($adminA);
        $fees = Fee::all();

        $this->assertCount(1, $fees);
        $this->assertEquals('500.00', $fees->first()->amount);
    }

    public function test_super_admin_sees_all_students(): void
    {
        $franchiseA = $this->makeFranchise('Franchise A');
        $franchiseB = $this->makeFranchise('Franchise B');

        $this->makeStudentWithUser($franchiseA);
        $this->makeStudentWithUser($franchiseB);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin);
        $students = Student::all();

        $this->assertCount(2, $students);
    }
}
