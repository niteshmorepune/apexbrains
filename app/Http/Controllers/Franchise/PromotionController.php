<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\Level;
use App\Models\Student;
use App\Models\StudentLevel;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        // A student is eligible once they've passed a submitted exam for their CURRENT level.
        $eligible = Student::with(['currentLevel', 'examAttempts.exam'])
            ->where('is_active', true)
            ->where('student_type', 'internal')
            ->get()
            ->filter(function ($s) {
                if (! $s->currentLevel || $s->currentLevel->number >= 14) {
                    return false;
                }
                return $s->examAttempts->contains(fn($a) =>
                    $a->status === 'submitted' && $a->is_passed
                    && $a->exam && (int) $a->exam->level_id === (int) $s->current_level_id);
            })
            ->map(function ($s) {
                $latest = $s->examAttempts
                    ->where('status', 'submitted')
                    ->where('is_passed', true)
                    ->filter(fn($a) => $a->exam && (int) $a->exam->level_id === (int) $s->current_level_id)
                    ->sortByDesc('submitted_at')
                    ->first();

                $s->exam_score    = $latest?->percentage ?? 0;
                $s->exam_speed    = $latest ? round($latest->submitted_at?->diffInSeconds($latest->started_at) ?? 0) : null;
                $s->exam_accuracy = $latest?->percentage ?? 0;
                $s->exam_attempts = $s->examAttempts->where('status', 'submitted')->count();
                return $s;
            })
            ->values();

        $levels = Level::orderBy('number')->get();

        return view('franchise.promotions.index', compact('eligible', 'levels'));
    }

    public function promote(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'new_level_id' => ['required', 'exists:levels,id'],
        ]);

        if (! $this->hasPassedCurrentLevel($student)) {
            return back()->with('error',
                "{$student->full_name} has not passed the Level {$student->currentLevel?->number} exam yet, so cannot be promoted.");
        }

        $this->applyPromotion($student, (int) $data['new_level_id']);

        return back()->with('success',
            "{$student->full_name} promoted to Level " . Level::find($data['new_level_id'])->number . '.');
    }

    /**
     * Promote every eligible student to their next level in one action.
     * Eligibility is re-verified per student (passed current-level exam).
     */
    public function promoteBatch(): RedirectResponse
    {
        $students = Student::with('currentLevel')
            ->where('is_active', true)
            ->where('student_type', 'internal')
            ->get();

        $promoted = 0;
        foreach ($students as $student) {
            if (! $student->currentLevel || $student->currentLevel->number >= 14) {
                continue;
            }
            if (! $this->hasPassedCurrentLevel($student)) {
                continue;
            }

            $nextLevel = Level::where('number', $student->currentLevel->number + 1)->first();
            if (! $nextLevel) {
                continue;
            }

            $this->applyPromotion($student, $nextLevel->id);
            $promoted++;
        }

        if ($promoted === 0) {
            return back()->with('error', 'No eligible students to promote.');
        }

        return back()->with('success', "Batch promoted {$promoted} student" . ($promoted === 1 ? '' : 's') . '.');
    }

    /**
     * A student may be promoted only after passing the exam for their CURRENT level.
     */
    protected function hasPassedCurrentLevel(Student $student): bool
    {
        if (! $student->current_level_id) {
            return false;
        }

        return ExamAttempt::where('student_id', $student->id)
            ->where('status', 'submitted')
            ->where('is_passed', true)
            ->whereHas('exam', fn($q) => $q->where('level_id', $student->current_level_id))
            ->exists();
    }

    protected function applyPromotion(Student $student, int $newLevelId): void
    {
        $oldLevelId = $student->current_level_id;

        StudentLevel::create([
            'student_id'  => $student->id,
            'level_id'    => $newLevelId,
            'franchise_id'=> Auth::user()->franchise_id,
            'assigned_at' => now(),
            'promoted_at' => now(),
            'promoted_by' => Auth::id(),
        ]);

        $student->update(['current_level_id' => $newLevelId]);

        AuditLogger::log('student_promoted', 'Student', $student->id, ['level_id' => $oldLevelId], ['level_id' => $newLevelId]);
    }
}
