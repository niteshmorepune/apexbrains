<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Level;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $history = AuditLog::where('franchise_id', Auth::user()->franchise_id)
            ->where('action', 'like', '%notification%')
            ->latest('created_at')
            ->paginate(20);

        $levels   = Level::orderBy('number')->get();
        $students = Student::where('is_active', true)->orderBy('first_name')->get();

        return view('franchise.notifications.index', compact('history', 'levels', 'students'));
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'message'    => ['required', 'string', 'max:500'],
            'channel'    => ['required', 'in:whatsapp,sms,both'],
            'target'     => ['required', 'in:all,level,student'],
            'level_id'   => ['nullable', 'exists:levels,id'],
            'student_id' => ['nullable', 'exists:students,id'],
        ]);

        // Real delivery in Phase 6 — log the intent
        $targetDesc = match($data['target']) {
            'level'   => 'Level ' . Level::find($data['level_id'])?->number . ' students',
            'student' => Student::find($data['student_id'])?->full_name,
            default   => 'All students',
        };

        AuditLogger::log('notification_sent', "Notification sent via {$data['channel']} to {$targetDesc}");

        return back()->with('success', "Notification queued for {$targetDesc} via {$data['channel']}.");
    }
}
