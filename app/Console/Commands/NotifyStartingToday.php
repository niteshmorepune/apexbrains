<?php

namespace App\Console\Commands;

use App\Models\ApexNotification;
use App\Models\Competition;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Console\Command;

class NotifyStartingToday extends Command
{
    protected $signature = 'apex:notify-starting-today';

    protected $description = 'Notify students whose competition or exam starts today (IST)';

    public function handle(): int
    {
        $this->notifyCompetitions();
        $this->notifyExams();

        return self::SUCCESS;
    }

    private function notifyCompetitions(): void
    {
        $competitions = Competition::where('is_active', true)
            ->whereDate('start_date', today('Asia/Kolkata'))
            ->get();

        foreach ($competitions as $competition) {
            $studentsQuery = Student::where('is_active', true)
                ->whereIn('id', $competition->registrations()->pluck('student_id'));

            $students = $studentsQuery->get();

            if ($students->isEmpty()) {
                continue;
            }

            ApexNotification::notifyStudents(
                $students,
                'competition_starting_today',
                'Competition Starts Today: ' . $competition->title,
                "\"{$competition->title}\" starts today. Good luck!"
            );
        }
    }

    private function notifyExams(): void
    {
        $exams = Exam::where('is_active', true)
            ->whereNotNull('scheduled_at')
            ->get()
            ->filter(fn (Exam $exam) => $exam->scheduled_at->timezone('Asia/Kolkata')->isToday());

        foreach ($exams as $exam) {
            $students = Student::where('current_level_id', $exam->level_id)
                ->where('student_type', 'internal')
                ->where('is_active', true)
                ->get();

            if ($students->isEmpty()) {
                continue;
            }

            ApexNotification::notifyStudents(
                $students,
                'exam_starting_today',
                'Exam Starts Today: ' . $exam->title,
                "Your exam \"{$exam->title}\" is scheduled for today. Get ready!"
            );
        }
    }
}
