<?php

namespace Database\Seeders;

use App\Models\ApexNotification;
use App\Models\Certificate;
use App\Models\Competition;
use App\Models\CompetitionQuestionPaper;
use App\Models\CompetitionRegistration;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Level;
use App\Models\LevelUpExamPaper;
use App\Models\LevelUpExamPaperItem;
use App\Models\PracticeSession;
use App\Models\Student;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Idempotent demo data for a client walkthrough of the (internal) Student Portal.
 * Targets arjun@student.in. Safe to re-run.
 *
 *   php artisan db:seed --class=StudentWalkthroughSeeder --force
 */
class StudentWalkthroughSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'arjun@student.in')->first();
        $student = $user?->student()->withoutGlobalScopes()->first();
        if (! $student) {
            $this->command?->warn('arjun@student.in student not found — skipping.');
            return;
        }

        $fid   = $student->franchise_id;
        $admin = User::where('franchise_id', $fid)->whereHas('roles', fn ($q) => $q->where('name', 'franchise_admin'))->first()
              ?? User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();

        $levelBy = fn (int $n) => Level::where('number', $n)->first();
        $cur     = $levelBy(5) ?? Level::orderBy('number')->first();

        // 1) Put arjun at a mid level so the journey + history look real.
        $student->update(['current_level_id' => $cur->id]);

        // 2) Level progression history (levels 1..5 assigned; 1..4 promoted).
        foreach (range(1, 5) as $n) {
            $lvl = $levelBy($n);
            if (! $lvl) continue;
            StudentLevel::firstOrCreate(
                ['student_id' => $student->id, 'level_id' => $lvl->id],
                [
                    'franchise_id' => $fid,
                    'assigned_at'  => now()->subMonths(6 - $n),
                    'promoted_at'  => $n < 5 ? now()->subMonths(5 - $n) : null,
                    'promoted_by'  => $admin?->id,
                ]
            );
        }

        // 3) Certificates for completed levels 3 & 4.
        foreach ([3, 4] as $n) {
            $lvl = $levelBy($n);
            if (! $lvl) continue;
            Certificate::firstOrCreate(
                ['certificate_number' => 'ARJ-CERT-L' . $n],
                [
                    'franchise_id'      => $fid,
                    'student_id'        => $student->id,
                    'level_id'          => $lvl->id,
                    'verification_code' => (string) Str::uuid(),
                    'type'              => 'level_completion',
                    'series'            => 'A',
                    'issued_at'         => now()->subMonths(5 - $n)->toDateString(),
                    'issued_by'         => $admin?->id,
                ]
            );
        }

        // 4) Upcoming exam at the current level.
        Exam::withoutGlobalScopes()->firstOrCreate(
            ['title' => 'Level ' . $cur->number . ' Practice 3', 'franchise_id' => $fid],
            [
                'level_id' => $cur->id, 'duration_minutes' => 10, 'total_questions' => 20,
                'pass_percentage' => 75, 'max_attempts' => 3, 'is_active' => true,
                'scheduled_at' => now()->addDays(3)->setTime(16, 0), 'created_by' => $admin?->id,
            ]
        );

        // 5) Past exams with submitted attempts (+ answers for the breakdown).
        $past = [
            ['Level ' . $cur->number . ' Mid-Term', $cur->id, 88, 36],
            ['Level ' . $cur->number . ' Practice 2', $cur->id, 84, 53],
            ['Level 4 Final', $levelBy(4)?->id ?? $cur->id, 92, 84],
        ];
        foreach ($past as [$title, $levelId, $pct, $daysAgo]) {
            $this->seedPastExam($student, $fid, $admin, $title, $levelId, $pct, $daysAgo);
        }

        // 6) Notifications (Today + Earlier This Week — matches Figma S58).
        $notes = [
            ['exam_result', 'Exam Result Day', 'Level ' . $cur->number . ' Mid-Term — You scored 88%! View full report.', 0],
            ['practice', 'Practice Reminder', "Complete today's practice session to maintain your streak.", 0],
            ['exam_scheduled', 'New Exam Scheduled', 'Level ' . $cur->number . ' Final Exam — ' . now()->addDays(3)->format('d M Y') . ' at 4:00 PM', 2],
            ['certificate', 'Certificate Earned!', 'Level 4 certificate is ready. Download or share it now.', 3],
            ['fee', 'Fee Payment Confirmed', now()->format('M Y') . ' fee payment recorded. Receipt available.', 4],
        ];
        foreach ($notes as [$type, $titleN, $msg, $daysAgo]) {
            $n = ApexNotification::firstOrCreate(
                ['student_id' => $student->id, 'title' => $titleN],
                [
                    'franchise_id' => $fid, 'type' => $type, 'message' => $msg,
                    'channel' => 'app', 'is_read' => $daysAgo > 0,
                    'read_at' => $daysAgo > 0 ? now()->subDays($daysAgo) : null,
                    'sent_at' => now()->subDays($daysAgo),
                ]
            );
            // Place it in the right time bucket for the grouped view.
            $n->forceFill(['created_at' => now()->subDays($daysAgo)->subHours(2)])->save();
        }

        // 7) Practice sessions across recent days (streak + recent list + chart).
        foreach (range(0, 4) as $d) {
            $code = 'ARJ-PS-' . $d;
            if (PracticeSession::where('student_id', $student->id)->where('difficulty', $code)->exists()) continue;
            $correct = 132 + $d * 4;
            $ps = PracticeSession::create([
                'student_id' => $student->id, 'level_id' => $cur->id,
                'difficulty' => null, 'total_questions' => 150,
                'questions_correct' => min($correct, 150),
                'accuracy' => round(min($correct, 150) / 150 * 100, 2),
                'avg_speed_seconds' => 4.2 - $d * 0.1, 'duration_minutes' => 10,
                'completed_at' => now()->subDays($d)->setTime(14, 30),
            ]);
            // Tag created_at so streak/day-grouping works, without a usable difficulty column.
            $ps->forceFill(['created_at' => now()->subDays($d)->setTime(14, 20)])->save();
        }

        // 8) Competitions — one upcoming (registered + playable paper) + two past.
        $cup = Competition::firstOrCreate(
            ['title' => 'Global Abacus Masters Cup', 'franchise_id' => $fid],
            [
                'description' => 'Annual national-level abacus championship.',
                'competition_type' => 'national',
                'start_date' => now()->addDays(10)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'registration_deadline' => now()->addDays(5)->toDateString(),
                'fee_amount' => 0, 'is_active' => true, 'is_open_to_external' => true,
                'created_by' => $admin?->id,
            ]
        );
        CompetitionRegistration::firstOrCreate(
            ['competition_id' => $cup->id, 'student_id' => $student->id],
            [
                'franchise_id' => $fid, 'student_type' => 'internal',
                'registration_date' => now()->toDateString(), 'payment_status' => 'paid',
                'status' => 'registered', 'registered_by' => $admin?->id,
            ]
        );
        // A playable question paper for arjun's level.
        $paper = CompetitionQuestionPaper::firstOrCreate(
            ['competition_id' => $cup->id, 'level_id' => $cur->id],
            [
                'title' => 'Level ' . $cur->number . ' — Masters Cup Paper',
                'total_questions' => 10, 'duration_minutes' => 10, 'pass_percentage' => 75,
                'is_active' => true, 'created_by' => $admin?->id,
            ]
        );
        if ($paper->items()->count() === 0) {
            $items = [
                ['12 + 45 + 33', '90', '80', '95', '70', 'a'],
                ['100 - 25 - 15', '60', '70', '55', '65', 'a'],
                ['8 × 7', '54', '56', '64', '49', 'b'],
                ['144 ÷ 12', '11', '13', '12', '14', 'c'],
                ['25 + 75 + 50', '140', '150', '160', '120', 'b'],
                ['18 + 27 + 9', '54', '45', '63', '52', 'a'],
                ['200 - 88', '102', '122', '112', '118', 'c'],
                ['15 × 6', '80', '90', '95', '85', 'b'],
                ['64 + 36', '100', '90', '110', '96', 'a'],
                ['81 ÷ 9', '7', '8', '9', '11', 'c'],
            ];
            foreach ($items as $i => [$q, $a, $b, $c, $d, $ans]) {
                $paper->items()->create([
                    'question_text' => $q, 'option_a' => $a, 'option_b' => $b,
                    'option_c' => $c, 'option_d' => $d, 'correct_answer' => $ans, 'sort_order' => $i,
                ]);
            }
            $paper->update(['total_questions' => count($items)]);
        }

        foreach ([['National Mental Math Competition', 18], ['Speed Challenge', 33]] as [$title, $daysAgo]) {
            $pastComp = Competition::firstOrCreate(
                ['title' => $title, 'franchise_id' => $fid],
                [
                    'description' => 'Past competition.', 'competition_type' => 'regional',
                    'start_date' => now()->subDays($daysAgo)->toDateString(),
                    'end_date' => now()->subDays($daysAgo)->toDateString(),
                    'registration_deadline' => now()->subDays($daysAgo + 5)->toDateString(),
                    'fee_amount' => 0, 'is_active' => true, 'is_open_to_external' => true,
                    'created_by' => $admin?->id,
                ]
            );
            CompetitionRegistration::firstOrCreate(
                ['competition_id' => $pastComp->id, 'student_id' => $student->id],
                [
                    'franchise_id' => $fid, 'student_type' => 'internal',
                    'registration_date' => now()->subDays($daysAgo + 3)->toDateString(),
                    'payment_status' => 'paid', 'status' => 'registered', 'registered_by' => $admin?->id,
                ]
            );
        }

        // ===== External (competition-only) student: external@test.in =====
        $extUser    = User::where('email', 'external@test.in')->first();
        $extStudent = $extUser?->student()->withoutGlobalScopes()->first();
        if ($extStudent) {
            $efid   = $extStudent->franchise_id;
            // External students have no curriculum level, so their practice
            // history is PracticeSession rows (External\PracticeController),
            // not level-keyed CompetitionPracticeAttempt rows.
            $placeholderLevel = Level::orderBy('number')->value('id');
            foreach (range(0, 2) as $idx) {
                $completedAt = now()->subDays(3 - $idx)->setTime(11, 10);
                if (PracticeSession::where('student_id', $extStudent->id)->whereDate('completed_at', $completedAt->toDateString())->exists()) continue;
                $tot = 10;
                $sc  = min($tot, 8 + $idx);
                $ps = PracticeSession::create([
                    'student_id' => $extStudent->id, 'level_id' => $placeholderLevel,
                    'difficulty' => null, 'total_questions' => $tot,
                    'questions_correct' => $sc, 'accuracy' => round($sc / $tot * 100, 2),
                    'avg_speed_seconds' => 5.0, 'duration_minutes' => 10,
                    'completed_at' => now()->subDays(3 - $idx)->setTime(11, 10),
                ]);
                $ps->forceFill(['created_at' => now()->subDays(3 - $idx)->setTime(11, 0)])->save();
            }

            CompetitionRegistration::firstOrCreate(
                ['competition_id' => $cup->id, 'student_id' => $extStudent->id],
                ['franchise_id' => $efid, 'student_type' => 'external', 'registration_date' => now()->toDateString(),
                 'payment_status' => 'paid', 'status' => 'registered', 'registered_by' => $admin?->id]
            );

            Certificate::firstOrCreate(
                ['certificate_number' => 'EXT-CERT-COMP-1'],
                ['franchise_id' => $efid, 'student_id' => $extStudent->id, 'competition_id' => $cup->id,
                 'verification_code' => (string) Str::uuid(), 'type' => 'competition', 'series' => 'C',
                 'issued_at' => now()->subDays(20)->toDateString(), 'issued_by' => $admin?->id]
            );

            foreach ([
                ['competition', 'Competition Registration Confirmed', 'You have successfully registered for Global Abacus Masters Cup', 0],
                ['practice', 'Practice Reminder', "Don't miss out! Complete today's practice session and stay consistent.", 0],
                ['competition', 'New Competition Announced', 'Inter Academy Abacus Battle registrations are now open.', 2],
                ['certificate', 'Certificate Available', 'Your participation certificate is ready to download.', 3],
                ['practice', 'Practice Paper Completed', 'Great job! You completed Practice Paper 12.', 4],
            ] as [$type, $title, $msg, $d]) {
                $n = ApexNotification::firstOrCreate(
                    ['student_id' => $extStudent->id, 'title' => $title],
                    ['franchise_id' => $efid, 'type' => $type, 'message' => $msg, 'channel' => 'app',
                     'is_read' => $d > 0, 'read_at' => $d > 0 ? now()->subDays($d) : null, 'sent_at' => now()->subDays($d)]
                );
                $n->forceFill(['created_at' => now()->subDays($d)->subHours(2)])->save();
            }
        }

        $this->command?->info("Student walkthrough demo data ready for {$user->email} (level {$cur->number}) + external@test.in.");
    }

    private function seedPastExam(Student $student, int $fid, ?User $admin, string $title, int $levelId, int $pct, int $daysAgo): void
    {
        $exam = Exam::withoutGlobalScopes()->firstOrCreate(
            ['title' => $title, 'franchise_id' => $fid],
            [
                'level_id' => $levelId, 'duration_minutes' => 10, 'total_questions' => 10,
                'pass_percentage' => 75, 'max_attempts' => 3, 'is_active' => true,
                'scheduled_at' => now()->subDays($daysAgo)->setTime(16, 0),
                'expires_at' => now()->subDays($daysAgo - 1)->setTime(18, 0),
                'created_by' => $admin?->id,
            ]
        );

        $existing = ExamAttempt::where('exam_id', $exam->id)->where('student_id', $student->id)->first();
        if ($existing) return;

        $paper = LevelUpExamPaper::firstOrCreate(
            ['exam_id' => $exam->id],
            ['title' => 'Demo Paper', 'is_active' => true, 'created_by' => $admin?->id]
        );
        if ($paper->items()->count() === 0) {
            for ($i = 1; $i <= 10; $i++) {
                LevelUpExamPaperItem::create([
                    'paper_id' => $paper->id,
                    'question_text' => "Demo question {$i}",
                    'option_a' => '1', 'option_b' => '2', 'option_c' => '3', 'option_d' => '4',
                    'correct_answer' => 'a',
                    'sort_order' => $i,
                ]);
            }
            $paper->update(['total_questions' => 10]);
            $exam->update(['total_questions' => 10]);
        }

        $questions = $paper->items()->orderBy('sort_order')->limit(10)->get(['id', 'correct_answer']);
        if ($questions->isEmpty()) return;

        $correct = (int) round($pct / 10);
        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id, 'student_id' => $student->id, 'franchise_id' => $fid,
            'attempt_number' => 1, 'question_ids' => $questions->pluck('id')->toArray(),
            'started_at' => now()->subDays($daysAgo)->setTime(16, 0),
            'status' => 'submitted', 'score' => $correct, 'percentage' => $pct,
            'is_passed' => $pct >= 75, 'submitted_at' => now()->subDays($daysAgo)->setTime(16, 8),
            'ip_address' => '127.0.0.1', 'user_agent' => 'StudentWalkthroughSeeder',
            'tab_switch_count' => 0, 'fullscreen_exit_count' => 0,
        ]);

        foreach ($questions as $i => $q) {
            $isCorrect = $i < $correct;
            $wrong = collect(['a', 'b', 'c', 'd'])->reject(fn ($o) => $o === $q->correct_answer)->first();
            ExamAnswer::create([
                'exam_attempt_id' => $attempt->id, 'question_id' => $q->id,
                'selected_answer' => $isCorrect ? $q->correct_answer : $wrong,
                'is_correct' => $isCorrect, 'time_taken_seconds' => rand(6, 25),
                'answered_at' => now()->subDays($daysAgo)->setTime(16, 1 + $i),
            ]);
        }
    }
}
