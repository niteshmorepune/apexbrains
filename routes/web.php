<?php

use Illuminate\Support\Facades\Route;

// Root redirect based on role (controller — closures break route:cache)
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index']);

// Student login (internal + external)
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Self-service password reset — shared across all 4 portals (single `users` table/guard)
Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware(['guest', 'throttle:6,1']);
Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update')->middleware(['guest', 'throttle:6,1']);

// Public certificate verification — no auth required
Route::get('/verify/{verification_code}', [\App\Http\Controllers\CertificateVerifyController::class, 'show'])->name('certificate.verify');
