<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ApexNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $notifications = ApexNotification::where('student_id', $student->id)
            ->latest()
            ->paginate(20);

        // Mark unread notifications as read
        ApexNotification::where('student_id', $student->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('student.notifications.index', compact('notifications'));
    }
}
