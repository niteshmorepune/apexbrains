<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_internal_students' => Student::where('student_type', 'internal')->count(),
            'total_external_students' => Student::where('student_type', 'external')->count(),
            'active_franchises'       => Franchise::where('status', 'active')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
