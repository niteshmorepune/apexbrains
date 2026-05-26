<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\Franchise;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesFranchise;
use Tests\TestCase;

class FeePaymentTest extends TestCase
{
    use RefreshDatabase, CreatesFranchise;

    private Franchise $franchise;
    private User $admin;
    private Student $student;
    private Fee $fee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->franchise = $this->makeFranchise();
        $this->admin     = $this->makeFranchiseAdmin($this->franchise);

        [, $this->student] = $this->makeStudentWithUser($this->franchise);
        $this->fee = $this->makeFee($this->franchise, $this->student, 1000);
    }

    public function test_full_payment_marks_fee_as_paid(): void
    {
        $this->actingAs($this->admin)
            ->post(route('franchise.payments.store'), [
                'fee_id'       => $this->fee->id,
                'amount'       => 1000,
                'payment_mode' => 'cash',
                'payment_date' => now()->toDateString(),
            ]);

        $this->fee->refresh();
        $this->assertEquals('paid', $this->fee->status);
        $this->assertEquals('1000.00', $this->fee->paid_amount);
    }

    public function test_partial_payment_marks_fee_as_partial(): void
    {
        $this->actingAs($this->admin)
            ->post(route('franchise.payments.store'), [
                'fee_id'       => $this->fee->id,
                'amount'       => 400,
                'payment_mode' => 'upi',
                'payment_date' => now()->toDateString(),
            ]);

        $this->fee->refresh();
        $this->assertEquals('partial', $this->fee->status);
        $this->assertEquals('400.00', $this->fee->paid_amount);
    }

    public function test_payment_creates_receipt_with_correct_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('franchise.payments.store'), [
                'fee_id'                => $this->fee->id,
                'amount'                => 1000,
                'payment_mode'          => 'upi',
                'payment_date'          => now()->toDateString(),
                'transaction_reference' => 'UPI-12345',
            ]);

        $payment = Payment::withoutGlobalScopes()
            ->where('fee_id', $this->fee->id)->first();

        $this->assertNotNull($payment);
        $this->assertEquals('1000.00', $payment->amount);
        $this->assertEquals('UPI-12345', $payment->transaction_reference);
        $this->assertNotNull($payment->receipt_number);
        $this->assertEquals($this->admin->id, $payment->recorded_by);
    }

    public function test_payment_redirects_to_receipt(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('franchise.payments.store'), [
                'fee_id'       => $this->fee->id,
                'amount'       => 1000,
                'payment_mode' => 'cash',
                'payment_date' => now()->toDateString(),
            ]);

        $payment = Payment::withoutGlobalScopes()->where('fee_id', $this->fee->id)->first();
        $response->assertRedirectToRoute('franchise.payments.receipt', $payment);
    }

    public function test_guest_cannot_record_payment(): void
    {
        $this->post(route('franchise.payments.store'), [
            'fee_id'       => $this->fee->id,
            'amount'       => 1000,
            'payment_mode' => 'cash',
            'payment_date' => now()->toDateString(),
        ])->assertRedirectToRoute('login');
    }
}
