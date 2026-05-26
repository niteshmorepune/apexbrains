<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LearningPathController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->with('currentLevel', 'levels.level')->first();
        $levels  = Level::where('is_active', true)->orderBy('number')->get();

        $completedLevelIds = $student
            ? $student->levels->pluck('level_id')->toArray()
            : [];

        $currentLevelNumber = $student?->currentLevel?->number ?? 0;

        return view('student.learning-path.index', compact('levels', 'student', 'completedLevelIds', 'currentLevelNumber'));
    }

    public function levelOverview(Level $level): View
    {
        $student = Auth::user()->student()->with('currentLevel')->first();

        $exams = collect();
        if ($student) {
            $exams = \App\Models\Exam::where('franchise_id', $student->franchise_id)
                ->where('level_id', $level->id)
                ->where('is_active', true)
                ->orderBy('scheduled_at')
                ->get();
        }

        $myAttempts = $student
            ? $student->examAttempts()
                ->whereHas('exam', fn($q) => $q->where('level_id', $level->id))
                ->with('exam')
                ->latest()
                ->get()
            : collect();

        $currentLevelNumber = $student?->currentLevel?->number ?? 0;

        return view('student.learning-path.show', compact('level', 'student', 'exams', 'myAttempts', 'currentLevelNumber'));
    }
}
