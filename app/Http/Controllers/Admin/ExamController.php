<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Level;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with('level')->withCount('attempts')->latest()->paginate(20);

        return view('admin.exams.index', compact('exams'));
    }

    public function create(): View
    {
        $levels = Level::where('is_active', true)->orderBy('number')->get();

        return view('admin.exams.create', compact('levels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateExam($request);

        $exam = Exam::create([
            ...$data,
            'franchise_id' => null, // global — applies to all franchises
            'total_questions' => 0, // derived once a question paper is uploaded
            'is_active'    => $request->boolean('is_active', true),
            'created_by'   => Auth::id(),
        ]);

        AuditLogger::log('exam_created', 'Exam', $exam->id);

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', "Exam '{$exam->title}' created. Upload a question paper to make it attemptable.");
    }

    public function show(Exam $exam): View
    {
        $exam->load('level', 'activePaper');

        $attemptCount = $exam->attempts()->count();
        $passCount    = $exam->attempts()->where('is_passed', true)->count();
        $avgScore     = $exam->attempts()->whereNotNull('percentage')->avg('percentage');

        $recentAttempts = $exam->attempts()
            ->with('student')
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        return view('admin.exams.show', compact('exam', 'attemptCount', 'passCount', 'avgScore', 'recentAttempts'));
    }

    public function edit(Exam $exam): View
    {
        $levels = Level::where('is_active', true)->orderBy('number')->get();

        return view('admin.exams.edit', compact('exam', 'levels'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $this->validateExam($request);

        $exam->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log('exam_updated', 'Exam', $exam->id);

        return redirect()->route('admin.exams.show', $exam)
            ->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        if ($exam->attempts()->exists()) {
            return back()->with('error', 'Cannot delete an exam that has attempts.');
        }

        $exam->delete();

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam deleted.');
    }

    private function validateExam(Request $request): array
    {
        return $request->validate([
            'title'            => ['required', 'string', 'max:150'],
            'level_id'         => ['required', 'exists:levels,id'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'pass_percentage'  => ['required', 'numeric', 'min:1', 'max:100'],
            'max_attempts'     => ['nullable', 'integer', 'min:1', 'max:255'],
            'scheduled_at'     => ['nullable', 'date'],
            'expires_at'       => ['nullable', 'date', 'after:scheduled_at'],
            'description'      => ['nullable', 'string', 'max:500'],
        ]);
    }
}
