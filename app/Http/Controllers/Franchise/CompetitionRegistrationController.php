<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompetitionRegistrationController extends Controller
{
    public function index(): View
    {
        $franchiseId = Auth::user()->franchise_id;

        // Show only active competitions: this franchise's own plus any
        // admin-created (global) competitions that have no franchise_id.
        $competitions = Competition::where('is_active', true)
            ->where(function ($q) use ($franchiseId) {
                $q->whereNull('franchise_id')
                  ->orWhere('franchise_id', $franchiseId);
            })
            ->with(['registrations.student'])
            ->orderByDesc('start_date')
            ->get();

        $students = Student::where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('franchise.competitions.index', compact('competitions', 'students'));
    }

    public function store(Request $request, Competition $competition): RedirectResponse
    {
        $franchiseId = Auth::user()->franchise_id;

        // Allow registering into this franchise's own competitions and into
        // admin-created (global, franchise_id = null) competitions.
        if ($competition->franchise_id !== null && $competition->franchise_id !== $franchiseId) {
            abort(403);
        }

        if (! $competition->is_active) {
            return back()->with('error', 'This competition is not active.');
        }

        if ($competition->registration_deadline && $competition->registration_deadline->isPast()) {
            return back()->with('error', 'The registration deadline for this competition has passed.');
        }

        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);

        $student = Student::findOrFail($request->student_id);

        if ($student->franchise_id !== $franchiseId) {
            return back()->withErrors(['student_id' => 'Student does not belong to your franchise.']);
        }

        $alreadyRegistered = CompetitionRegistration::where('competition_id', $competition->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($alreadyRegistered) {
            return back()->with('error', "{$student->full_name} is already registered for this competition.");
        }

        if ($competition->max_participants) {
            $count = $competition->registrations()->count();
            if ($count >= $competition->max_participants) {
                return back()->with('error', 'Competition has reached maximum participants.');
            }
        }

        CompetitionRegistration::create([
            'competition_id'    => $competition->id,
            'student_id'        => $student->id,
            'franchise_id'      => $franchiseId,
            'student_type'      => $student->student_type,
            'registration_date' => now()->toDateString(),
            'payment_status'    => 'pending',
            'registered_by'     => Auth::id(),
            'status'            => 'registered',
        ]);

        AuditLogger::log('competition_student_registered', 'CompetitionRegistration', $competition->id);

        return back()->with('success', "{$student->full_name} registered for '{$competition->title}'.");
    }
}
