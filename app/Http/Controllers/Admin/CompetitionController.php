<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CompetitionController extends Controller
{
    public function index(): View
    {
        return view('admin.competitions.index');
    }
}
