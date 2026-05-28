<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(): RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('super_admin'))    return redirect()->route('admin.dashboard');
            if ($user->hasRole('franchise_admin')) return redirect()->route('franchise.dashboard');
            if ($user->hasRole('student') && $user->student_type === 'external') return redirect()->route('external.home');
            if ($user->hasRole('student'))         return redirect()->route('student.home');
        }

        return redirect()->route('login');
    }
}
