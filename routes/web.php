<?php

use Illuminate\Support\Facades\Route;

// Root redirect based on role (controller — closures break route:cache)
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index']);

// Student login (internal + external)
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Public certificate verification — no auth required
Route::get('/verify/{verification_code}', [\App\Http\Controllers\CertificateVerifyController::class, 'show'])->name('certificate.verify');
