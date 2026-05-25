<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPracticePaper;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student()->first();
        $totalPapers = CompetitionPracticePaper::where('is_active', true)->count();

        return view('external.home', compact('student', 'totalPapers'));
    }
}
