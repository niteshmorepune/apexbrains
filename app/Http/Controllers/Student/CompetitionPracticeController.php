<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CompetitionPracticeController extends Controller
{
    public function index(): View
    {
        return view('student.competitionpractice');
    }
}
