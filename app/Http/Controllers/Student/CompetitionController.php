<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionExamAttempt;
use App\Models\CompetitionQuestionPaper;
use App\Models\CompetitionRegistration;
use App\Services\CompetitionRegistrationFeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function __construct(private CompetitionRegistrationFeeService $feeService)
    {
    }

    public function index(): View
    {
        $student = Auth::user()->student()->firstOrFail();
        $today   = now()->toDateString();

        $myRegistrationIds = CompetitionRegistration::where('student_id', $student->id)
            ->pluck('competition_id')
            ->toArray();

        // Once the student has submitted their own attempt, the competition
        // is "Completed" for them regardless of the competition's own
        // end_date — a student shouldn't see something they already
        // finished sitting under "Upcoming".
        $mySubmittedCompetitionIds = CompetitionExamAttempt::where('student_id', $student->id)
            ->where('status', 'submitted')
            ->pluck('competition_id')
            ->toArray();

        // Competitions visible to the student: their franchise's own + admin-created
        // global (franchise_id null) competitions, plus anything they're already
        // registered for (e.g. registered by the franchise).
        $visibleScope = function ($q) use ($student, $myRegistrationIds) {
            $q->whereNull('franchise_id')
              ->orWhere('franchise_id', $student->franchise_id)
              ->orWhereIn('id', $myRegistrationIds);
        };

        // Upcoming = active, not yet ended, and not already completed by this
        // student. Registration may still be open OR the student is already
        // registered, so franchise-registered competitions stay visible even
        // after their registration deadline has passed.
        $competitions = Competition::where('is_active', true)
            ->where($visibleScope)
            ->whereNotIn('id', $mySubmittedCompetitionIds)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->where(function ($q) use ($today, $myRegistrationIds) {
                $q->whereNull('registration_deadline')
                  ->orWhere('registration_deadline', '>=', $today)
                  ->orWhereIn('id', $myRegistrationIds);
            })
            ->orderBy('start_date')
            ->get();

        $pastCompetitions = Competition::where($visibleScope)
            ->where(function ($q) use ($today, $mySubmittedCompetitionIds) {
                $q->where('end_date', '<', $today)
                  ->orWhereIn('id', $mySubmittedCompetitionIds);
            })
            ->orderByDesc('end_date')
            ->limit(5)
            ->get();

        return view('student.competitions.index', compact(
            'competitions', 'myRegistrationIds', 'pastCompetitions', 'mySubmittedCompetitionIds'
        ));
    }

    public function show(Competition $competition): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $registration = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->first();

        $paper       = $this->paperForStudent($competition, $student);
        $myAttempts  = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->where('status', 'submitted')
            ->get();

        return view('student.competitions.show', compact('competition', 'registration', 'myAttempts', 'paper'));
    }

    public function startExam(Request $request, Competition $competition): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if (! $this->isRegistered($competition, $student)) {
            return back()->with('error', 'You are not registered for this competition.');
        }

        $today = now()->toDateString();
        if ($competition->start_date && $competition->start_date->toDateString() > $today) {
            return back()->with('error', 'This competition has not started yet. It opens on ' . $competition->start_date->format('d M Y') . '.');
        }
        if ($competition->end_date && $competition->end_date->toDateString() < $today) {
            return back()->with('error', 'This competition has ended.');
        }

        $paper = $this->paperForStudent($competition, $student);

        if (! $paper || $paper->items()->count() === 0) {
            return back()->with('error', 'No question paper is available for your level yet. Please contact your branch.');
        }

        // Resume an in-progress attempt rather than starting a duplicate.
        $attempt = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (! $attempt) {
            $attempt = CompetitionExamAttempt::create([
                'paper_id'       => $paper->id,
                'competition_id' => $competition->id,
                'student_id'     => $student->id,
                'started_at'     => now(),
                'status'         => 'in_progress',
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);

            Cache::put("comp_exam_{$attempt->id}_answers", [], now()->addHours(3));
        }

        return redirect()->route('student.competitions.attempt', $competition);
    }

    public function attempt(Competition $competition): View|RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (! $attempt) {
            return redirect()->route('student.competitions.show', $competition);
        }

        $paper     = $attempt->paper;
        $questions = $this->questionPayload($paper);

        $savedAnswers    = Cache::get("comp_exam_{$attempt->id}_answers", []);
        $durationSeconds = $paper->duration_minutes * 60;
        $elapsed         = now()->diffInSeconds($attempt->started_at, true);
        $remaining       = max(0, $durationSeconds - $elapsed);

        if ($remaining === 0) {
            return $this->doSubmit($attempt, $paper);
        }

        return view('student.competitions.attempt', compact(
            'competition', 'paper', 'attempt', 'questions', 'savedAnswers', 'remaining', 'durationSeconds'
        ));
    }

    public function saveAnswer(Request $request, Competition $competition): JsonResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        $data = $request->validate([
            'question_id'     => ['required', 'integer'],
            'selected_answer' => ['required', 'in:a,b,c,d'],
        ]);

        $answers = Cache::get("comp_exam_{$attempt->id}_answers", []);
        $answers[$data['question_id']] = $data['selected_answer'];
        Cache::put("comp_exam_{$attempt->id}_answers", $answers, now()->addHours(3));

        return response()->json(['saved' => true]);
    }

    public function submitExam(Request $request, Competition $competition): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        return $this->doSubmit($attempt, $attempt->paper);
    }

    public function result(Competition $competition): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $attempt = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->where('status', 'submitted')
            ->with('paper')
            ->latest('submitted_at')
            ->first();

        $certificate = null;
        $rank = null;

        // Score, rank, and the participation certificate only surface once the
        // admin has declared results for this competition.
        if ($attempt && $competition->results_declared_at) {
            $certificate = \App\Models\Certificate::where('student_id', $student->id)
                ->where('competition_id', $competition->id)
                ->where('type', 'competition')
                ->where('is_revoked', false)
                ->latest()
                ->first();

            $rank = CompetitionExamAttempt::where('competition_id', $competition->id)
                ->where('status', 'submitted')
                ->where(function ($q) use ($attempt) {
                    $q->where('percentage', '>', $attempt->percentage)
                      ->orWhere(function ($q2) use ($attempt) {
                          $q2->where('percentage', $attempt->percentage)
                             ->where('submitted_at', '<', $attempt->submitted_at);
                      });
                })
                ->count() + 1;
        }

        return view('student.competitions.result', compact('competition', 'attempt', 'certificate', 'rank'));
    }

    public function register(Request $request, Competition $competition): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if ($this->isRegistered($competition, $student)) {
            return back()->with('error', 'You are already registered.');
        }

        $registration = CompetitionRegistration::create([
            'competition_id'    => $competition->id,
            'student_id'        => $student->id,
            'franchise_id'      => $student->franchise_id,
            'student_type'      => $student->student_type,
            'status'            => 'registered',
            'payment_status'    => 'pending',
            'registration_date' => now()->toDateString(),
            'registered_by'     => Auth::id(),
        ]);

        $this->feeService->createFeeFor($registration, $competition);

        return back()->with('success', "Registered for {$competition->title}!");
    }

    /**
     * The active competition paper for the student's exact level. Papers are
     * authored per level, so a Level 5 student only ever sits the Level 5 paper —
     * no fallback to another level's paper.
     */
    protected function paperForStudent(Competition $competition, $student): ?CompetitionQuestionPaper
    {
        return CompetitionQuestionPaper::where('competition_id', $competition->id)
            ->where('is_active', true)
            ->where('level_id', $student->current_level_id)
            ->first();
    }

    protected function isRegistered(Competition $competition, $student): bool
    {
        return CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->exists();
    }

    /**
     * Shape paper items for the Alpine attempt engine WITHOUT leaking the
     * correct answer to the browser.
     */
    protected function questionPayload(CompetitionQuestionPaper $paper)
    {
        return $paper->items()
            ->orderBy('sort_order')
            ->get(['id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d'])
            ->map(fn ($item) => ['question' => $item]);
    }

    protected function doSubmit(CompetitionExamAttempt $attempt, CompetitionQuestionPaper $paper): RedirectResponse
    {
        if ($attempt->status === 'submitted') {
            return redirect()->route('student.competitions.result', $attempt->competition_id);
        }

        $answers = Cache::get("comp_exam_{$attempt->id}_answers", []);
        $items   = $paper->items()->get(['id', 'correct_answer']);
        $correct = 0;

        foreach ($items as $item) {
            if (isset($answers[$item->id]) && strtolower($answers[$item->id]) === strtolower($item->correct_answer)) {
                $correct++;
            }
        }

        $total = $items->count();
        $pct   = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        $attempt->update([
            'score'        => $correct,
            'percentage'   => $pct,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        Cache::forget("comp_exam_{$attempt->id}_answers");

        // Certificate issuance now happens when the admin declares results
        // (Admin\CompetitionController::declareResults), not at submit time.

        return redirect()->route('student.competitions.result', $attempt->competition_id);
    }
}
