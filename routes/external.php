<?php

use Illuminate\Support\Facades\Route;

Route::prefix('external')->name('external.')->middleware(['auth', 'external.student'])->group(function () {
    Route::get('/', [\App\Http\Controllers\External\HomeController::class, 'index'])->name('home');

    // Help Guide
    Route::view('help', 'external.help')->name('help');

    // Competition Practice Papers (all 50 papers)
    Route::get('practice/hub', [\App\Http\Controllers\External\CompetitionPracticeController::class, 'hub'])->name('practice.hub');
    Route::get('practice', [\App\Http\Controllers\External\CompetitionPracticeController::class, 'index'])->name('practice.index');
    Route::post('practice/{paper}/start', [\App\Http\Controllers\External\CompetitionPracticeController::class, 'start'])->name('practice.start');
    Route::get('practice/{paper}/attempt', [\App\Http\Controllers\External\CompetitionPracticeController::class, 'attempt'])->name('practice.attempt');
    Route::post('practice/{paper}/answer', [\App\Http\Controllers\External\CompetitionPracticeController::class, 'saveAnswer'])->name('practice.answer');
    Route::post('practice/{paper}/submit', [\App\Http\Controllers\External\CompetitionPracticeController::class, 'submit'])->name('practice.submit');
    Route::get('practice/{paper}/result', [\App\Http\Controllers\External\CompetitionPracticeController::class, 'result'])->name('practice.result');

    // Results (past practice attempts)
    Route::get('results', [\App\Http\Controllers\External\CompetitionPracticeController::class, 'resultHistory'])->name('results');

    // Competitions
    Route::get('competitions', [\App\Http\Controllers\External\CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('competitions/{competition}', [\App\Http\Controllers\External\CompetitionController::class, 'show'])->name('competitions.show');

    // Certificates
    Route::get('certificates', [\App\Http\Controllers\External\CertificateController::class, 'index'])->name('certificates.index');
    Route::get('certificates/{certificate}', [\App\Http\Controllers\External\CertificateController::class, 'show'])->name('certificates.show');
    Route::get('certificates/{certificate}/pdf', [\App\Http\Controllers\External\CertificateController::class, 'downloadPdf'])->name('certificates.pdf');

    // Profile (read-only + change password)
    Route::get('profile', [\App\Http\Controllers\External\ProfileController::class, 'index'])->name('profile');
    Route::put('profile/password', [\App\Http\Controllers\External\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\External\NotificationController::class, 'index'])->name('notifications.index');
});
