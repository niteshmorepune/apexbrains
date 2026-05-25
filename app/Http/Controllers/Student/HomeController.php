<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->with('currentLevel')->first();

        return view('student.home', compact('student'));
    }
}
