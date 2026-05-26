<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\ApexNotification;
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
        $franchiseId = Auth::user()->franchise_id;

        $history = ApexNotification::where('franchise_id', $franchiseId)
            ->latest()
            ->paginate(20);

        $levels   = Level::orderBy('number')->get();
        $students = Student::where('is_active', true)->orderBy('first_name')->get();

        return view('franchise.notifications.index', compact('history', 'levels', 'students'));
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:150'],
            'message'    => ['required', 'string', 'max:500'],
            'channel'    => ['required', 'in:app,whatsapp,sms,email'],
            'target'     => ['required', 'in:all,level,student'],
            'level_id'   => ['nullable', 'exists:levels,id'],
            'student_id' => ['nullable', 'exists:students,id'],
        ]);

        $franchiseId = Auth::user()->franchise_id;

        $targetStudents = match($data['target']) {
            'level'   => Student::where('current_level_id', $data['level_id'])->where('is_active', true)->get(),
            'student' => Student::where('id', $data['student_id'])->get(),
            default   => Student::where('is_active', true)->get(),
        };

        foreach ($targetStudents as $student) {
            ApexNotification::create([
                'franchise_id' => $franchiseId,
                'student_id'   => $student->id,
                'user_id'      => Auth::id(),
                'type'         => 'franchise_message',
                'title'        => $data['title'],
                'message'      => $data['message'],
                'channel'      => $data['channel'],
                'sent_at'      => now(),
            ]);
        }

        AuditLogger::log('notification_sent', 'ApexNotification');

        $count = $targetStudents->count();

        return back()->with('success', "Notification sent to {$count} student(s).");
    }
}
