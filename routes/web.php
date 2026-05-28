<?php

use Illuminate\Support\Facades\Route;

// Root redirect based on role
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('super_admin')) return redirect()->route('admin.dashboard');
        if ($user->hasRole('franchise_admin')) return redirect()->route('franchise.dashboard');
        if ($user->hasRole('student') && $user->student_type === 'external') return redirect()->route('external.home');
        if ($user->hasRole('student')) return redirect()->route('student.home');
    }
    return redirect()->route('login');
});

// Student login (internal + external)
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Public certificate verification — no auth required
Route::get('/verify/{verification_code}', [\App\Http\Controllers\CertificateVerifyController::class, 'show'])->name('certificate.verify');
