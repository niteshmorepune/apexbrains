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

        $scope = function ($q) use ($student) {
            $q->where('franchise_id', $student->franchise_id)
              ->where(function ($q) use ($student) {
                  $q->whereNull('student_id')->orWhere('student_id', $student->id);
              });
        };

        $notifications = ApexNotification::where($scope)->latest('created_at')->paginate(20);

        ApexNotification::where($scope)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('external.notifications.index', compact('notifications'));
    }
}
