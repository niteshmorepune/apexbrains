<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPracticeSessionGrant;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CompetitionPracticeAccessController extends Controller
{
    private const INITIAL_SESSIONS = 50;
    private const TOP_UP_SESSIONS = 10;

    public function toggle(Student $student): RedirectResponse
    {
        $enabling = ! $student->competition_practice_access;
        $student->competition_practice_access = $enabling;

        if ($enabling && $student->competition_practice_sessions_allowed === 0) {
            $student->competition_practice_sessions_allowed = self::INITIAL_SESSIONS;

            CompetitionPracticeSessionGrant::create([
                'student_id' => $student->id,
                'granted_by' => Auth::id(),
                'sessions_granted' => self::INITIAL_SESSIONS,
                'note' => 'Initial grant (access enabled)',
            ]);
        }

        $student->save();

        AuditLogger::log(
            $enabling ? 'competition_practice_access_enabled' : 'competition_practice_access_disabled',
            'Student',
            $student->id
        );

        return back()->with('success', $enabling
            ? "Competition Practice access enabled for {$student->full_name}."
            : "Competition Practice access disabled for {$student->full_name}.");
    }

    public function grant(Student $student): RedirectResponse
    {
        $student->increment('competition_practice_sessions_allowed', self::TOP_UP_SESSIONS);

        CompetitionPracticeSessionGrant::create([
            'student_id' => $student->id,
            'granted_by' => Auth::id(),
            'sessions_granted' => self::TOP_UP_SESSIONS,
            'note' => 'Additional sessions granted',
        ]);

        AuditLogger::log('competition_practice_sessions_granted', 'Student', $student->id);

        return back()->with('success', self::TOP_UP_SESSIONS . " additional Competition Practice sessions granted to {$student->full_name}.");
    }
}
