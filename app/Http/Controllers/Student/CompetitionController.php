<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $competitions = Competition::where('franchise_id', $student->franchise_id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('registration_deadline')
                  ->orWhere('registration_deadline', '>=', now()->toDateString());
            })
            ->orderBy('start_date')
            ->get();

        $myRegistrationIds = CompetitionRegistration::where('student_id', $student->id)
            ->pluck('competition_id')
            ->toArray();

        $pastCompetitions = Competition::where('franchise_id', $student->franchise_id)
            ->where('end_date', '<', now()->toDateString())
            ->orderByDesc('end_date')
            ->limit(5)
            ->get();

        return view('student.competitions.index', compact(
            'competitions', 'myRegistrationIds', 'pastCompetitions'
        ));
    }

    public function register(Request $request, Competition $competition): RedirectResponse
    {
        $student = Auth::user()->student()->firstOrFail();

        $already = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($already) {
            return back()->with('error', 'You are already registered.');
        }

        CompetitionRegistration::create([
            'competition_id' => $competition->id,
            'student_id'     => $student->id,
            'franchise_id'   => $student->franchise_id,
            'status'          => 'registered',
            'registration_date' => now()->toDateString(),
        ]);

        return back()->with('success', "Registered for {$competition->title}!");
    }
}
