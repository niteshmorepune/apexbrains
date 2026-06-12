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

        $openCompetitions = Competition::where('is_open_to_external', true)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->orderBy('start_date')
            ->get();

        $registeredIds = $myRegistrations->pluck('competition_id')->toArray();

        $pastCompetitions = Competition::where('is_open_to_external', true)
            ->where('end_date', '<', now()->toDateString())
            ->orderByDesc('end_date')
            ->limit(5)
            ->get();

        return view('external.competitions.index', compact(
            'myRegistrations', 'openCompetitions', 'registeredIds', 'pastCompetitions'
        ));
    }

    public function show(Competition $competition): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $myRegistration = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->first();

        $paper      = $this->paperForCompetition($competition);
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

        $paper = $this->paperForCompetition($competition);

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

        $savedAnswers = Cache::get("ext_comp_{$attempt->id}_answers", []);
        $remaining    = max(0, ($paper->duration_minutes * 60) - now()->diffInSeconds($attempt->started_at));

        if ($remaining === 0) {
            return $this->doSubmit($attempt, $paper);
        }

        return view('external.competitions.attempt', compact(
            'competition', 'paper', 'attempt', 'questions', 'savedAnswers', 'remaining'
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

        $certificate = \App\Models\Certificate::where('student_id', $student->id)
            ->where('competition_id', $competition->id)
            ->where('type', 'competition')
            ->where('is_revoked', false)
            ->latest()
            ->first();

        return view('external.competitions.result', compact('competition', 'attempt', 'certificate'));
    }

    /**
     * External students have no curriculum level, so they sit the competition's
     * available paper (the lowest-level active paper) rather than a level match.
     */
    protected function paperForCompetition(Competition $competition): ?CompetitionQuestionPaper
    {
        return CompetitionQuestionPaper::where('competition_id', $competition->id)
            ->where('is_active', true)
            ->orderBy('level_id')
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

        $student = $attempt->student;
        if ($student && $attempt->competition) {
            app(CertificateIssuer::class)->issueForCompetition(
                $student, $attempt->competition, $student->user_id
            );
        }

        return redirect()->route('external.competitions.result', $attempt->competition_id);
    }
}
