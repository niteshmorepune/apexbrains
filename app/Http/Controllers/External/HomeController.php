<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\PracticeSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->first();

        $recentSessions = $student
            ? PracticeSession::where('student_id', $student->id)
                ->whereNotNull('completed_at')
                ->latest('completed_at')
                ->limit(3)
                ->get()
            : collect();

        return view('external.home', compact('student', 'recentSessions'));
    }
}
