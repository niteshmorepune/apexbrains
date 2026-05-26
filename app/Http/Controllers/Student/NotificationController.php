<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $notifications = AuditLog::where('franchise_id', $student->franchise_id)
            ->where('action', 'like', '%notification%')
            ->latest('created_at')
            ->paginate(20);

        return view('student.notifications.index', compact('notifications'));
    }
}
