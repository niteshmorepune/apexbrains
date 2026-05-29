<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\ApexNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->firstOrFail();

        $notifications = ApexNotification::where('franchise_id', $student->franchise_id)
            ->where(function ($q) use ($student) {
                $q->whereNull('student_id')
                  ->orWhere('student_id', $student->id);
            })
            ->latest('created_at')
            ->paginate(20);

        return view('external.notifications.index', compact('notifications'));
    }
}
