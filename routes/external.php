<?php

use Illuminate\Support\Facades\Route;

Route::prefix('external')->name('external.')->middleware(['auth', 'external.student'])->group(function () {
    Route::get('/', [\App\Http\Controllers\External\HomeController::class, 'index'])->name('home');

    // Help Guide
    Route::view('help', 'external.help')->name('help');

    // Practice — random questions generated from the Question Bank (by difficulty)
    Route::get('practice', [\App\Http\Controllers\External\PracticeController::class, 'index'])->name('practice.index');
    Route::post('practice/start', [\App\Http\Controllers\External\PracticeController::class, 'start'])->name('practice.start');
    Route::get('practice/{session}', [\App\Http\Controllers\External\PracticeController::class, 'session'])->name('practice.session');
    Route::post('practice/{session}/answer', [\App\Http\Controllers\External\PracticeController::class, 'answer'])->name('practice.answer');
    Route::post('practice/{session}/submit', [\App\Http\Controllers\External\PracticeController::class, 'submit'])->name('practice.submit');
    Route::get('practice/{session}/results', [\App\Http\Controllers\External\PracticeController::class, 'results'])->name('practice.results');

    // Results (past practice sessions)
    Route::get('results', [\App\Http\Controllers\External\PracticeController::class, 'history'])->name('results');

    // Competitions — external students can now sit them in-app
    Route::get('competitions', [\App\Http\Controllers\External\CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('competitions/{competition}/show', [\App\Http\Controllers\External\CompetitionController::class, 'show'])->name('competitions.show');
    Route::post('competitions/{competition}/start', [\App\Http\Controllers\External\CompetitionController::class, 'startExam'])->name('competitions.start');
    Route::get('competitions/{competition}/attempt', [\App\Http\Controllers\External\CompetitionController::class, 'attempt'])->name('competitions.attempt');
    Route::post('competitions/{competition}/answer', [\App\Http\Controllers\External\CompetitionController::class, 'saveAnswer'])->name('competitions.answer')->middleware('throttle:120,1');
    Route::post('competitions/{competition}/submit', [\App\Http\Controllers\External\CompetitionController::class, 'submitExam'])->name('competitions.submit');
    Route::get('competitions/{competition}/result', [\App\Http\Controllers\External\CompetitionController::class, 'result'])->name('competitions.result');

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
