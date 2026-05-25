<?php

namespace App\Http\Controllers\Franchise;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $franchiseId = Auth::user()->franchise_id;

        $stats = [
            'internal_students' => Student::where('franchise_id', $franchiseId)->where('student_type', 'internal')->count(),
            'external_students' => Student::where('franchise_id', $franchiseId)->where('student_type', 'external')->count(),
        ];

        return view('franchise.dashboard', compact('stats'));
    }
}
