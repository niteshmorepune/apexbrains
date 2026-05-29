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

    public function show(Competition $competition): View
    {
        $student = Auth::user()->student()->firstOrFail();
        $registration = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->first();
        // Competition exams run through the practice-paper engine — track via paper attempts
        $myAttempts = \App\Models\CompetitionPracticeAttempt::where('student_id', $student->id)
            ->where('status', 'submitted')
            ->get();

        return view('student.competitions.show', compact('competition', 'registration', 'myAttempts'));
    }

    public function startExam(Request $request, Competition $competition): RedirectResponse
    {
        // Competition exams are delivered through the competition practice papers
        return redirect()->route('student.competitions.practice');
    }

    public function attempt(Competition $competition): RedirectResponse
    {
        return redirect()->route('student.competitions.practice');
    }

    public function saveAnswer(Request $request, Competition $competition): \Illuminate\Http\JsonResponse
    {
        return response()->json(['ok' => true]);
    }

    public function submitExam(Request $request, Competition $competition): RedirectResponse
    {
        return redirect()->route('student.competitions.result', $competition);
    }

    public function result(Competition $competition): View
    {
        $student = Auth::user()->student()->firstOrFail();
        $attempt = \App\Models\CompetitionPracticeAttempt::where('student_id', $student->id)
            ->where('status', 'submitted')
            ->with('paper')
            ->latest('submitted_at')
            ->first();

        return view('student.competitions.result', compact('competition', 'attempt'));
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
