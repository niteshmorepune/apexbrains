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
        $eligible = Student::with(['currentLevel',
            'examAttempts' => fn($q) => $q->where('status', 'submitted')->latest()->limit(1)])
            ->where('is_active', true)
            ->whereHas('examAttempts', fn($q) => $q->where('is_passed', true)->where('status', 'submitted'))
            ->get()
            ->filter(fn($s) => $s->currentLevel && $s->currentLevel->number < 14)
            ->map(function ($s) {
                $latest = $s->examAttempts->first();
                $s->exam_score    = $latest?->percentage ?? 0;
                $s->exam_speed    = $latest ? round($latest->submitted_at?->diffInSeconds($latest->started_at) ?? 0) : null;
                $s->exam_accuracy = $latest?->percentage ?? 0;
                $s->exam_attempts = ExamAttempt::where('student_id', $s->id)->where('status', 'submitted')->count();
                return $s;
            });

        $levels = Level::orderBy('number')->get();

        return view('franchise.promotions.index', compact('eligible', 'levels'));
    }

    public function promote(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'new_level_id' => ['required', 'exists:levels,id'],
        ]);

        $oldLevelId = $student->current_level_id;

        StudentLevel::create([
            'student_id'  => $student->id,
            'level_id'    => $data['new_level_id'],
            'franchise_id'=> Auth::user()->franchise_id,
            'assigned_at' => now(),
            'promoted_at' => now(),
            'promoted_by' => Auth::id(),
        ]);

        $student->update(['current_level_id' => $data['new_level_id']]);

        AuditLogger::log('student_promoted', 'Student', $student->id, ['level_id' => $oldLevelId], ['level_id' => $data['new_level_id']]);

        return back()->with('success', "{$student->full_name} promoted to Level " . Level::find($data['new_level_id'])->number . '.');
    }
}
