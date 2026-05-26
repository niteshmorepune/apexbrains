<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use Illuminate\Support\Facades\Auth;
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

        return view('external.competitions.index', compact('myRegistrations', 'openCompetitions', 'registeredIds'));
    }

    public function show(Competition $competition): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $myRegistration = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->first();

        return view('external.competitions.show', compact('competition', 'myRegistration'));
    }
}
