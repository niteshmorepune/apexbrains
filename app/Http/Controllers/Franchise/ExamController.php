<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $exams = Exam::with('level')
            ->latest()
            ->paginate(20);

        return view('franchise.exams.index', compact('exams'));
    }

    public function create(): View
    {
        $levels = Level::where('is_active', true)->orderBy('number')->get();

        return view('franchise.exams.create', compact('levels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'           => ['required', 'string', 'max:150'],
            'level_id'        => ['required', 'exists:levels,id'],
            'total_questions' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_minutes'=> ['required', 'integer', 'min:5', 'max:180'],
            'pass_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'max_attempts'    => ['nullable', 'integer', 'min:1'],
            'scheduled_at'    => ['nullable', 'date'],
            'expires_at'      => ['nullable', 'date', 'after:scheduled_at'],
            'description'     => ['nullable', 'string', 'max:500'],
        ]);

        $available = QuestionBank::where('level_id', $data['level_id'])
            ->where('status', 'approved')
            ->count();

        if ($available < $data['total_questions']) {
            return back()->withErrors(['total_questions' => "Only {$available} approved questions available for this level."])->withInput();
        }

        $exam = Exam::create([
            ...$data,
            'franchise_id' => Auth::user()->franchise_id,
            'is_active'    => true,
            'created_by'   => Auth::id(),
        ]);

        AuditLogger::log('exam_created', 'Exam', $exam->id);

        return redirect()->route('franchise.exams.show', $exam)
            ->with('success', "Exam '{$exam->title}' created.");
    }

    public function show(Exam $exam): View
    {
        $exam->load('level');

        $attemptCount = $exam->attempts()->count();
        $passCount    = $exam->attempts()->where('is_passed', true)->count();
        $avgScore     = $exam->attempts()->whereNotNull('percentage')->avg('percentage');

        $recentAttempts = $exam->attempts()
            ->with('student')
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        return view('franchise.exams.show', compact('exam', 'attemptCount', 'passCount', 'avgScore', 'recentAttempts'));
    }

    public function edit(Exam $exam): View
    {
        $levels = Level::where('is_active', true)->orderBy('number')->get();

        return view('franchise.exams.edit', compact('exam', 'levels'));
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $data = $request->validate([
            'title'           => ['required', 'string', 'max:150'],
            'level_id'        => ['required', 'exists:levels,id'],
            'total_questions' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_minutes'=> ['required', 'integer', 'min:5', 'max:180'],
            'pass_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'max_attempts'    => ['nullable', 'integer', 'min:1'],
            'scheduled_at'    => ['nullable', 'date'],
            'expires_at'      => ['nullable', 'date'],
            'description'     => ['nullable', 'string', 'max:500'],
            'is_active'       => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $exam->update($data);
        AuditLogger::log('exam_updated', 'Exam', $exam->id);

        return redirect()->route('franchise.exams.show', $exam)
            ->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        if ($exam->attempts()->exists()) {
            return back()->with('error', 'Cannot delete an exam that has attempts.');
        }

        $exam->delete();

        return redirect()->route('franchise.exams.index')
            ->with('success', 'Exam deleted.');
    }
}
