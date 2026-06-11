<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\ClassPracticeResult;
use App\Models\ClassPracticeSession;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Fee;
use App\Models\Level;
use App\Models\Payment;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Idempotent demo data for a client walkthrough of the Franchise Panel.
 * Targets the Kothrud franchise (kothrud@apexbrains.in). Safe to re-run.
 *
 *   php artisan db:seed --class=FranchiseWalkthroughSeeder --force
 */
class FranchiseWalkthroughSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'kothrud@apexbrains.in')->first();
        if (! $admin || ! $admin->franchise_id) {
            $this->command?->warn('Kothrud franchise admin not found — skipping.');
            return;
        }
        $fid    = $admin->franchise_id;
        $levels = Level::orderBy('number')->take(4)->get();
        if ($levels->isEmpty()) {
            $this->command?->warn('No levels found — run LevelSeeder first.');
            return;
        }

        // 1) Ensure at least 5 active internal students in this franchise.
        $students = Student::withoutGlobalScopes()
            ->where('franchise_id', $fid)->where('student_type', 'internal')->where('is_active', true)
            ->orderBy('id')->get();

        $demoNames = [['Aarav', 'Sharma'], ['Diya', 'Patel'], ['Vivaan', 'Mehta'], ['Anaya', 'Joshi'], ['Reyansh', 'Kulkarni']];
        for ($i = 0; $students->count() < 5 && $i < count($demoNames); $i++) {
            $code = 'KTD-DEMO-' . ($i + 1);
            if (Student::withoutGlobalScopes()->where('student_code', $code)->exists()) {
                continue;
            }

            // Students require a linked login user (students.user_id is NOT NULL).
            $email = 'ktd.demo' . ($i + 1) . '@demo.apexbrains.in';
            $user  = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'         => $demoNames[$i][0] . ' ' . $demoNames[$i][1],
                    'password'     => Hash::make('password'),
                    'franchise_id' => $fid,
                    'student_type' => 'internal',
                ]
            );
            if (! $user->hasRole('student')) {
                $user->assignRole('student');
            }

            $students->push(Student::create([
                'franchise_id'     => $fid,
                'user_id'          => $user->id,
                'student_code'     => $code,
                'student_type'     => 'internal',
                'first_name'       => $demoNames[$i][0],
                'last_name'        => $demoNames[$i][1],
                'gender'           => $i % 2 ? 'female' : 'male',
                'date_of_birth'    => now()->subYears(8 + $i)->toDateString(),
                'enrollment_date'  => now()->subMonths(6)->toDateString(),
                'is_active'        => true,
                'current_level_id' => $levels[$i % $levels->count()]->id,
                'city'             => 'Pune',
            ]));
        }
        $students = $students->take(5)->values();

        // 1b) A primary parent each (drives Parent Directory + fee reminder WhatsApp).
        foreach ($students as $i => $s) {
            StudentParent::firstOrCreate(
                ['student_id' => $s->id, 'is_primary' => true],
                [
                    'name'         => $s->first_name . ' ' . $s->last_name . ' (Parent)',
                    'relationship' => $i % 2 ? 'mother' : 'father',
                    'phone'        => '98765' . str_pad((string) (43210 + $i), 5, '0', STR_PAD_LEFT),
                    'whatsapp'     => '98765' . str_pad((string) (43210 + $i), 5, '0', STR_PAD_LEFT),
                    'email'        => 'parent' . ($i + 1) . '.demo@example.com',
                ]
            );
        }

        // 2) Current-month monthly fees: mix of paid / pending / overdue.
        $month    = now()->startOfMonth();
        $statuses = ['paid', 'paid', 'pending', 'pending', 'overdue'];
        $fees     = collect();
        foreach ($students as $i => $s) {
            $st  = $statuses[$i % count($statuses)];
            $amt = 1500;
            $fee = Fee::firstOrCreate(
                ['student_id' => $s->id, 'fee_type' => 'monthly', 'month' => $month->toDateString()],
                [
                    'franchise_id' => $fid,
                    'amount'       => $amt,
                    'paid_amount'  => $st === 'paid' ? $amt : 0,
                    'status'       => $st,
                    'due_date'     => $st === 'overdue' ? now()->subDays(40)->toDateString() : now()->addDays(7)->toDateString(),
                ]
            );
            $fees->push($fee);
        }

        // 3) A recorded payment on the first paid fee → demonstrable receipt + PDF.
        $paidFee = $fees->firstWhere('status', 'paid');
        if ($paidFee) {
            Payment::firstOrCreate(
                ['receipt_number' => 'KTD-' . now()->year . '-DEMO1'],
                [
                    'franchise_id'          => $fid,
                    'student_id'            => $paidFee->student_id,
                    'fee_id'                => $paidFee->id,
                    'amount'                => $paidFee->amount,
                    'payment_mode'          => 'upi',
                    'transaction_reference' => 'DEMOUTR123',
                    'payment_date'          => now()->toDateString(),
                    'recorded_by'           => $admin->id,
                    'notes'                 => 'Demo payment for walkthrough',
                ]
            );
            $paidFee->update(['paid_amount' => $paidFee->amount, 'status' => 'paid']);
        }

        // 4) A certificate for the first student.
        $certStudent = $students->first();
        Certificate::firstOrCreate(
            ['certificate_number' => 'KTD-CERT-DEMO-1'],
            [
                'franchise_id'      => $fid,
                'student_id'        => $certStudent->id,
                'level_id'          => $certStudent->current_level_id,
                'verification_code' => (string) Str::uuid(),
                'type'              => 'level_completion',
                'series'            => 'A',
                'issued_at'         => now()->toDateString(),
                'issued_by'         => $admin->id,
            ]
        );

        // 5) An ended Class Practice session + result.
        $level = $levels->first();
        if (! ClassPracticeSession::withoutGlobalScopes()->where('franchise_id', $fid)->where('session_code', 'KTDEMO')->exists()) {
            $sess = ClassPracticeSession::create([
                'franchise_id'              => $fid,
                'teacher_id'                => $admin->id,
                'title'                     => 'Level ' . $level->number . ' Practice — Demo',
                'level_id'                  => $level->id,
                'question_category'         => 'level_practice',
                'total_questions'           => 20,
                'time_per_question_seconds' => 2,
                'audio_dictation'           => true,
                'status'                    => 'ended',
                'current_question_index'    => 20,
                'started_at'                => now()->subMinutes(15),
                'ended_at'                  => now()->subMinutes(5),
                'session_code'              => 'KTDEMO',
            ]);
            ClassPracticeResult::firstOrCreate(
                ['session_id' => $sess->id],
                ['franchise_id' => $fid, 'total_questions_shown' => 20, 'completed_at' => now()->subMinutes(5)]
            );
        }

        // 6) A franchise-scoped exam + a passed attempt (so Promotions lists a student
        //    and the read-only Exams page shows monitoring data).
        $promoStudent = $students->first(fn ($s) => optional($s->currentLevel)->number < 11) ?? $students->first();
        $exam = Exam::withoutGlobalScopes()->firstOrCreate(
            ['title' => 'Level ' . optional($promoStudent->currentLevel)->number . ' Assessment — Demo', 'franchise_id' => $fid],
            [
                'level_id'         => $promoStudent->current_level_id,
                'duration_minutes' => 30,
                'total_questions'  => 10,
                'pass_percentage'  => 60,
                'max_attempts'     => 3,
                'is_active'        => true,
                'created_by'       => $admin->id,
            ]
        );
        $qids = QuestionBank::where('status', 'approved')->inRandomOrder()->limit(10)->pluck('id')->toArray();
        ExamAttempt::firstOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $promoStudent->id],
            [
                'franchise_id'          => $fid,
                'attempt_number'        => 1,
                'question_ids'          => $qids,
                'started_at'            => now()->subMinutes(20),
                'status'                => 'submitted',
                'score'                 => 9,
                'percentage'            => 90,
                'is_passed'             => true,
                'submitted_at'          => now()->subMinutes(10),
                'ip_address'            => '127.0.0.1',
                'user_agent'            => 'WalkthroughSeeder',
                'tab_switch_count'      => 0,
                'fullscreen_exit_count' => 0,
            ]
        );

        // 7) An external (competition-only) student + a competition, registration,
        //    competition-registration fee, and a Participation certificate.
        $extUser = User::firstOrCreate(
            ['email' => 'ktd.ext.demo@demo.apexbrains.in'],
            [
                'name'         => 'Priya Nair',
                'password'     => Hash::make('password'),
                'franchise_id' => $fid,
                'student_type' => 'external',
            ]
        );
        if (! $extUser->hasRole('student')) {
            $extUser->assignRole('student');
        }

        $extStudent = Student::withoutGlobalScopes()->where('student_code', 'KTD-EXT-DEMO-1')->first();
        if (! $extStudent) {
            $extStudent = Student::create([
                'franchise_id'     => $fid,
                'user_id'          => $extUser->id,
                'student_code'     => 'KTD-EXT-DEMO-1',
                'student_type'     => 'external',
                'first_name'       => 'Priya',
                'last_name'        => 'Nair',
                'gender'           => 'female',
                'date_of_birth'    => now()->subYears(11)->toDateString(),
                'enrollment_date'  => now()->subMonths(2)->toDateString(),
                'is_active'        => true,
                'current_level_id' => null,
                'city'             => 'Pune',
            ]);
        }

        StudentParent::firstOrCreate(
            ['student_id' => $extStudent->id, 'is_primary' => true],
            [
                'name'         => 'Priya Nair (Parent)',
                'relationship' => 'mother',
                'phone'        => '9876500001',
                'whatsapp'     => '9876500001',
                'email'        => 'priya.parent.demo@example.com',
            ]
        );

        $competition = Competition::firstOrCreate(
            ['franchise_id' => $fid, 'title' => 'Apex Abacus Championship — Demo'],
            [
                'description'           => 'Demo competition for the franchise walkthrough.',
                'competition_type'      => 'regional',
                'start_date'            => now()->addDays(20)->toDateString(),
                'end_date'              => now()->addDays(20)->toDateString(),
                'registration_deadline' => now()->addDays(10)->toDateString(),
                'fee_amount'            => 500,
                'is_active'             => true,
                'is_open_to_external'   => true,
                'created_by'            => $admin->id,
            ]
        );

        CompetitionRegistration::firstOrCreate(
            ['competition_id' => $competition->id, 'student_id' => $extStudent->id],
            [
                'franchise_id'      => $fid,
                'student_type'      => 'external',
                'registration_date' => now()->toDateString(),
                'payment_status'    => 'paid',
                'registered_by'     => $admin->id,
                'status'            => 'confirmed',
            ]
        );

        // Competition-registration fee → demonstrates the "Competition Registration" fee type on receipts.
        Fee::firstOrCreate(
            ['student_id' => $extStudent->id, 'fee_type' => 'competition_registration', 'month' => $month->toDateString()],
            [
                'franchise_id' => $fid,
                'level_id'     => null,
                'student_type' => 'external',
                'amount'       => 500,
                'paid_amount'  => 0,
                'status'       => 'pending',
                'due_date'     => now()->addDays(7)->toDateString(),
            ]
        );

        // Participation certificate tied to the competition (type=competition, no level).
        Certificate::firstOrCreate(
            ['certificate_number' => 'KTD-CERT-PART-1'],
            [
                'franchise_id'      => $fid,
                'student_id'        => $extStudent->id,
                'level_id'          => null,
                'competition_id'    => $competition->id,
                'verification_code' => (string) Str::uuid(),
                'type'              => 'competition',
                'series'            => 'A',
                'issued_at'         => now()->toDateString(),
                'issued_by'         => $admin->id,
            ]
        );

        $this->command?->info('Franchise walkthrough demo data ready for franchise #' . $fid . '.');
    }
}
