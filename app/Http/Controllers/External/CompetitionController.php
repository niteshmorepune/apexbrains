<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionExamAttempt;
use App\Models\CompetitionQuestionPaper;
use App\Models\CompetitionRegistration;
use App\Services\CertificateIssuer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $myRegistrations = CompetitionRegistration::where('student_id', $student->id)
            ->with('competition')
            ->latest()
            ->get();

        // Once the student has submitted their own attempt, the competition
        // is "Completed" for them regardless of the competition's own
        // end_date — a student shouldn't see something they already
        // finished sitting under "Upcoming".
        $mySubmittedCompetitionIds = CompetitionExamAttempt::where('student_id', $student->id)
            ->where('status', 'submitted')
            ->pluck('competition_id')
            ->toArray();

        $today = now()->toDateString();

        $openCompetitions = Competition::where('is_open_to_external', true)
            ->where('is_active', true)
            ->whereNotIn('id', $mySubmittedCompetitionIds)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->orderBy('start_date')
            ->get();

        $registeredIds = $myRegistrations->pluck('competition_id')->toArray();

        $pastCompetitions = Competition::where('is_open_to_external', true)
            ->where(function ($q) use ($today, $mySubmittedCompetitionIds) {
                $q->where('end_date', '<', $today)
                  ->orWhereIn('id', $mySubmittedCompetitionIds);
            })
            ->orderByDesc('end_date')
            ->limit(5)
            ->get();

        return view('external.competitions.index', compact(
            'myRegistrations', 'openCompetitions', 'registeredIds', 'pastCompetitions', 'mySubmittedCompetitionIds'
        ));
    }

    public function show(Competition $competition): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $myRegistration = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->first();

        $paper      = $this->paperForStudent($competition, $student);
        $myAttempts = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->where('status', 'submitted')
            ->get();

        return view('external.competitions.show', compact('competition', 'myRegistration', 'paper', 'myAttempts'));
    }

    public function startExam(Request $request, Competition $competition): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        if (! $this->isRegistered($competition, $student)) {
            return back()->with('error', 'You are not registered for this competition. Ask your branch to register you.');
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
            return back()->with('error', 'No question paper is available for this competition yet. Please contact your branch.');
        }

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

            Cache::put("ext_comp_{$attempt->id}_answers", [], now()->addHours(3));
        }

        return redirect()->route('external.competitions.attempt', $competition);
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
            return redirect()->route('external.competitions.show', $competition);
        }

        $paper     = $attempt->paper;
        $questions = $paper->items()
            ->orderBy('sort_order')
            ->get(['id', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d'])
            ->map(fn ($item) => ['question' => $item]);

        $savedAnswers    = Cache::get("ext_comp_{$attempt->id}_answers", []);
        $durationSeconds = $paper->duration_minutes * 60;
        $remaining       = max(0, $durationSeconds - now()->diffInSeconds($attempt->started_at, true));

        if ($remaining === 0) {
            return $this->doSubmit($attempt, $paper);
        }

        return view('external.competitions.attempt', compact(
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

        $answers = Cache::get("ext_comp_{$attempt->id}_answers", []);
        $answers[$data['question_id']] = $data['selected_answer'];
        Cache::put("ext_comp_{$attempt->id}_answers", $answers, now()->addHours(3));

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

        // The participation certificate is available as soon as the student
        // submits. Rank (and champion/winner status) only surfaces once the
        // admin has declared results for this competition.
        if ($attempt) {
            $certificate = \App\Models\Certificate::where('student_id', $student->id)
                ->where('competition_id', $competition->id)
                ->whereIn('type', [\App\Models\Certificate::TYPE_PARTICIPATION, \App\Models\Certificate::TYPE_COMPETITION])
                ->where('is_revoked', false)
                ->latest()
                ->first();
        }

        if ($attempt && $competition->results_declared_at) {
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

        return view('external.competitions.result', compact('competition', 'attempt', 'certificate', 'rank'));
    }

    /**
     * The active competition paper for the student's actual registered level —
     * external students do have a current_level_id (set at registration), so
     * they sit the same level-matched paper as internal students. No fallback
     * to another level's paper.
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

    protected function doSubmit(CompetitionExamAttempt $attempt, CompetitionQuestionPaper $paper): RedirectResponse
    {
        if ($attempt->status === 'submitted') {
            return redirect()->route('external.competitions.result', $attempt->competition_id);
        }

        $answers = Cache::get("ext_comp_{$attempt->id}_answers", []);
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

        Cache::forget("ext_comp_{$attempt->id}_answers");

        // Participation certificate is automatic on successful submission — it
        // does not wait for results to be declared (only rank/champion/winner do).
        if ($attempt->student) {
            app(CertificateIssuer::class)->issueForCompetition(
                $attempt->student, $attempt->competition, null, null, null, $paper->level_id
            );
        }

        return redirect()->route('external.competitions.result', $attempt->competition_id);
    }
}
