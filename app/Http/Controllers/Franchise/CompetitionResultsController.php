<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Competition;
use App\Models\Level;
use App\Services\CertificateIssuer;
use App\Services\CompetitionRankingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompetitionResultsController extends Controller
{
    public function __construct(private CompetitionRankingService $rankingService)
    {
    }

    /**
     * Level-wise results for a competition, scoped to this franchise's own
     * students, with Champion (rank 1-3) / Winner (rank 4-6) generation actions.
     */
    public function show(Competition $competition): View
    {
        $franchiseId = Auth::user()->franchise_id;

        abort_unless($competition->franchise_id === null || $competition->franchise_id === $franchiseId, 403);

        $levels = $this->rankingService->levelsForCompetition($competition);

        $results = $levels->map(function (Level $level) use ($competition, $franchiseId) {
            $ranked = $this->rankingService->rankedAttemptsByLevel($competition, $level->id)
                ->filter(fn ($attempt) => $attempt->student && $attempt->student->franchise_id === $franchiseId)
                ->values();

            $issued = Certificate::where('competition_id', $competition->id)
                ->where('level_id', $level->id)
                ->whereIn('type', [Certificate::TYPE_CHAMPION, Certificate::TYPE_WINNER])
                ->pluck('type', 'student_id');

            return [
                'level'   => $level,
                'ranked'  => $ranked,
                'issued'  => $issued,
            ];
        })->filter(fn ($r) => $r['ranked']->isNotEmpty())->values();

        return view('franchise.competitions.results', compact('competition', 'results'));
    }

    public function generate(Competition $competition, Level $level, string $band): RedirectResponse
    {
        abort_unless(in_array($band, ['champion', 'winner'], true), 404);

        $franchiseId = Auth::user()->franchise_id;

        abort_unless($competition->franchise_id === null || $competition->franchise_id === $franchiseId, 403);

        if (! $competition->results_declared_at) {
            return back()->with('error', 'Results have not been declared for this competition yet.');
        }

        $ranked = $this->rankingService->rankedAttemptsByLevel($competition, $level->id)
            ->filter(fn ($attempt) => $attempt->student && $attempt->student->franchise_id === $franchiseId);

        $issuer = app(CertificateIssuer::class);
        $place  = Auth::user()->franchise?->city;

        $certs = $band === 'champion'
            ? $issuer->issueChampions($ranked, $competition, $level->id, Auth::id(), $place)
            : $issuer->issueWinners($ranked, $competition, $level->id, Auth::id(), $place);

        $count = $certs->count();

        return back()->with($count > 0 ? 'success' : 'error', $count > 0
            ? ucfirst($band) . " certificates generated for {$count} student(s) in {$level->title}."
            : 'No eligible students in that rank range for this level yet.');
    }
}
