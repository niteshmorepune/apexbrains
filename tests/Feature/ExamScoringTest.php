<?php

namespace Tests\Feature;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Exam;
use App\Models\Franchise;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesFranchise;
use Tests\TestCase;

class ExamScoringTest extends TestCase
{
    use RefreshDatabase, CreatesFranchise;

    private Franchise $franchise;
    private User $studentUser;
    private Student $student;
    private Exam $exam;
    private Level $level;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->franchise   = $this->makeFranchise();
        $this->level       = $this->makeLevel();
        [$this->studentUser, $this->student] = $this->makeStudentWithUser($this->franchise);

        $this->exam = Exam::withoutGlobalScopes()->create([
            'franchise_id'    => $this->franchise->id,
            'level_id'        => $this->level->id,
            'title'           => 'Test Exam',
            'total_questions' => 5,
            'duration_minutes'=> 30,
            'pass_percentage' => 60,
            'is_active'       => true,
            'created_by'      => $this->studentUser->id,
        ]);
    }

    private function makeAttemptWithAnswers(int $correct, int $total): ExamAttempt
    {
        $questions = [];
        for ($i = 1; $i <= $total; $i++) {
            $q = QuestionBank::create([
                'level_id'       => $this->level->id,
                'question_text'  => "Question {$i}",
                'type'           => 'mcq',
                'option_a'       => 'A', 'option_b' => 'B',
                'option_c'       => 'C', 'option_d'  => 'D',
                'correct_answer'    => 'a',
                'question_category'=> 'level_practice',
                'status'           => 'approved',
            ]);
            $questions[] = $q->id;
        }

        $attempt = ExamAttempt::create([
            'exam_id'              => $this->exam->id,
            'student_id'           => $this->student->id,
            'franchise_id'         => $this->franchise->id,
            'attempt_number'       => 1,
            'question_ids'         => $questions,
            'started_at'           => now()->subMinutes(5),
            'status'               => 'in_progress',
            'ip_address'           => '127.0.0.1',
            'user_agent'           => 'PHPUnit',
            'tab_switch_count'     => 0,
            'fullscreen_exit_count'=> 0,
        ]);

        foreach (array_slice($questions, 0, $correct) as $qid) {
            ExamAnswer::create([
                'exam_attempt_id' => $attempt->id,
                'question_id'     => $qid,
                'selected_answer' => 'a',
                'is_correct'      => true,
                'answered_at'     => now(),
            ]);
        }
        foreach (array_slice($questions, $correct) as $qid) {
            ExamAnswer::create([
                'exam_attempt_id' => $attempt->id,
                'question_id'     => $qid,
                'selected_answer' => 'b',
                'is_correct'      => false,
                'answered_at'     => now(),
            ]);
        }

        return $attempt;
    }

    public function test_passing_exam_sets_is_passed_true(): void
    {
        $this->makeAttemptWithAnswers(correct: 4, total: 5); // 80% — above 60%

        $this->actingAs($this->studentUser)
            ->post(route('student.exams.submit', $this->exam));

        $attempt = ExamAttempt::where('exam_id', $this->exam->id)
            ->where('student_id', $this->student->id)
            ->where('status', 'submitted')->first();

        $this->assertNotNull($attempt);
        $this->assertTrue((bool) $attempt->is_passed);
        $this->assertEquals(80.0, (float) $attempt->percentage);
        $this->assertEquals(4, (int) $attempt->score);
    }

    public function test_failing_exam_sets_is_passed_false(): void
    {
        $this->makeAttemptWithAnswers(correct: 2, total: 5); // 40% — below 60%

        $this->actingAs($this->studentUser)
            ->post(route('student.exams.submit', $this->exam));

        $attempt = ExamAttempt::where('exam_id', $this->exam->id)
            ->where('student_id', $this->student->id)
            ->where('status', 'submitted')->first();

        $this->assertNotNull($attempt);
        $this->assertFalse((bool) $attempt->is_passed);
        $this->assertEquals(40.0, (float) $attempt->percentage);
    }

    public function test_exactly_at_pass_mark_is_passing(): void
    {
        $this->makeAttemptWithAnswers(correct: 3, total: 5); // exactly 60%

        $this->actingAs($this->studentUser)
            ->post(route('student.exams.submit', $this->exam));

        $attempt = ExamAttempt::where('exam_id', $this->exam->id)
            ->where('student_id', $this->student->id)
            ->where('status', 'submitted')->first();

        $this->assertTrue((bool) $attempt->is_passed);
        $this->assertEquals(60.0, (float) $attempt->percentage);
    }

    public function test_zero_correct_answers_gives_zero_percentage(): void
    {
        $this->makeAttemptWithAnswers(correct: 0, total: 5);

        $this->actingAs($this->studentUser)
            ->post(route('student.exams.submit', $this->exam));

        $attempt = ExamAttempt::where('exam_id', $this->exam->id)
            ->where('student_id', $this->student->id)
            ->where('status', 'submitted')->first();

        $this->assertFalse((bool) $attempt->is_passed);
        $this->assertEquals(0.0, (float) $attempt->percentage);
    }
}
