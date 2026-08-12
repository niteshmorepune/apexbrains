<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApexNotification;
use App\Models\Competition;
use App\Models\CompetitionExamAttempt;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\CertificateIssuer;
use App\Services\CompetitionRankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function __construct(private CompetitionRankingService $rankingService)
    {
    }

    public function index(Request $request): View
    {
        $competitions = Competition::withCount(['registrations', 'questionPapers'])
            ->latest()->paginate(15)->withQueryString();

        return view('admin.competitions.index', compact('competitions'));
    }

    public function create(): View
    {
        return view('admin.competitions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                  => ['required', 'string', 'max:200'],
            'description'            => ['nullable', 'string'],
            'start_date'             => ['required', 'date'],
            'end_date'               => ['required', 'date', 'after_or_equal:start_date'],
            'registration_deadline'  => ['required', 'date', 'before_or_equal:start_date'],
            'max_participants'       => ['nullable', 'integer', 'min:1'],
            'fee_amount'             => ['required', 'numeric', 'min:0'],
            'duration_minutes'       => ['nullable', 'integer', 'min:1', 'max:600'],
            'is_open_to_external'    => ['boolean'],
            'is_active'              => ['boolean'],
        ]);

        $data['created_by']         = Auth::id();
        $data['is_active']          = $request->boolean('is_active', true);
        $data['is_open_to_external'] = $request->boolean('is_open_to_external', true);

        $competition = Competition::create($data);
        AuditLogger::log('competition_created', 'Competition', $competition->id);

        if ($competition->is_active) {
            $studentsQuery = Student::where('is_active', true);
            $studentsQuery = $competition->is_open_to_external
                ? $studentsQuery->whereIn('student_type', ['internal', 'external'])
                : $studentsQuery->where('student_type', 'internal');

            $when = $competition->start_date?->format('d M Y');

            ApexNotification::notifyStudents(
                $studentsQuery->get(),
                'competition_scheduled',
                'New Competition: ' . $competition->title,
                $when
                    ? "\"{$competition->title}\" starts on {$when}. Register before the deadline!"
                    : "\"{$competition->title}\" is now open for registration!"
            );
        }

        return redirect()->route('admin.competitions.show', $competition)
            ->with('success', "Competition '{$competition->title}' created.");
    }

    public function show(Competition $competition): View
    {
        $competition->loadCount('registrations');
        $competition->load(['questionPapers' => fn ($q) => $q->with('level')->withCount('items')->orderBy('level_id')]);

        // Champion/Winner ranks are determined separately per level, so results
        // are grouped by level here — same ranking service the franchise panel uses.
        $levels = $this->rankingService->levelsForCompetition($competition);

        $resultsByLevel = $levels->map(fn ($level) => [
            'level'  => $level,
            'ranked' => $this->rankingService->rankedAttemptsByLevel($competition, $level->id),
        ])->filter(fn ($r) => $r['ranked']->isNotEmpty())->values();

        $attempts = $resultsByLevel->flatMap(fn ($r) => $r['ranked']);

        return view('admin.competitions.show', compact('competition', 'attempts', 'resultsByLevel'));
    }

    public function declareResults(Competition $competition): RedirectResponse
    {
        if ($competition->results_declared_at) {
            return back()->with('error', 'Results have already been declared for this competition.');
        }

        $attempts = CompetitionExamAttempt::where('competition_id', $competition->id)
            ->where('status', 'submitted')
            ->with('student', 'paper')
            ->get();

        if ($attempts->isEmpty()) {
            return back()->with('error', 'No submitted attempts yet — nothing to declare.');
        }

        $competition->update(['results_declared_at' => now()]);

        // Backfill safety net — participation certificates are normally issued
        // automatically at submission time (Student/External CompetitionController).
        $issuer = app(CertificateIssuer::class);
        foreach ($attempts as $attempt) {
            if ($attempt->student) {
                $issuer->issueForCompetition($attempt->student, $competition, Auth::id(), null, null, $attempt->paper?->level_id);
            }
        }

        ApexNotification::notifyStudents(
            $attempts->pluck('student')->filter()->unique('id'),
            'competition_result',
            'Results Declared: ' . $competition->title,
            "Results for \"{$competition->title}\" have been declared. Check your rank now!"
        );

        AuditLogger::log('competition_results_declared', 'Competition', $competition->id);

        return back()->with('success', "Results declared for {$attempts->count()} student(s). Certificates issued.");
    }

    public function edit(Competition $competition): View
    {
        return view('admin.competitions.edit', compact('competition'));
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $data = $request->validate([
            'title'                  => ['required', 'string', 'max:200'],
            'description'            => ['nullable', 'string'],
            'start_date'             => ['required', 'date'],
            'end_date'               => ['required', 'date', 'after_or_equal:start_date'],
            'registration_deadline'  => ['required', 'date', 'before_or_equal:start_date'],
            'max_participants'       => ['nullable', 'integer', 'min:1'],
            'fee_amount'             => ['required', 'numeric', 'min:0'],
            'duration_minutes'       => ['nullable', 'integer', 'min:1', 'max:600'],
            'is_open_to_external'    => ['boolean'],
            'is_active'              => ['boolean'],
        ]);

        $data['is_active']           = $request->boolean('is_active');
        $data['is_open_to_external'] = $request->boolean('is_open_to_external');

        $competition->update($data);
        AuditLogger::log('competition_updated', 'Competition', $competition->id);

        return redirect()->route('admin.competitions.show', $competition)
            ->with('success', 'Competition updated.');
    }

    public function destroy(Competition $competition): RedirectResponse
    {
        $title = $competition->title;
        $competition->delete();
        AuditLogger::log('competition_deleted', 'Competition', null);

        return redirect()->route('admin.competitions.index')
            ->with('success', "Competition '{$title}' deleted.");
    }
}
