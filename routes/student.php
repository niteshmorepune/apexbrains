<?php

use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->middleware(['auth', 'internal.student'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Student\HomeController::class, 'index'])->name('home');

    // Learning Path
    Route::get('learning-path', [\App\Http\Controllers\Student\LearningPathController::class, 'index'])->name('learning-path');
    Route::get('levels/{level}', [\App\Http\Controllers\Student\LearningPathController::class, 'levelOverview'])->name('levels.show');

    // Practice
    Route::get('practice', [\App\Http\Controllers\Student\PracticeController::class, 'index'])->name('practice.index');
    Route::post('practice/start', [\App\Http\Controllers\Student\PracticeController::class, 'start'])->name('practice.start');
    Route::get('practice/{session}', [\App\Http\Controllers\Student\PracticeController::class, 'session'])->name('practice.session');
    Route::post('practice/{session}/answer', [\App\Http\Controllers\Student\PracticeController::class, 'answer'])->name('practice.answer');
    Route::post('practice/{session}/submit', [\App\Http\Controllers\Student\PracticeController::class, 'submit'])->name('practice.submit');
    Route::get('practice/{session}/results', [\App\Http\Controllers\Student\PracticeController::class, 'results'])->name('practice.results');

    // Exams
    Route::get('exams', [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('exams.index');
    Route::get('exams/{exam}', [\App\Http\Controllers\Student\ExamController::class, 'show'])->name('exams.show');
    Route::post('exams/{exam}/start', [\App\Http\Controllers\Student\ExamController::class, 'start'])->name('exams.start');
    Route::get('exams/{exam}/attempt', [\App\Http\Controllers\Student\ExamController::class, 'attempt'])->name('exams.attempt');
    Route::post('exams/{exam}/answer', [\App\Http\Controllers\Student\ExamController::class, 'saveAnswer'])->name('exams.answer')->middleware('throttle:120,1');
    Route::post('exams/{exam}/submit', [\App\Http\Controllers\Student\ExamController::class, 'submit'])->name('exams.submit');
    Route::get('exams/{exam}/result', [\App\Http\Controllers\Student\ExamController::class, 'result'])->name('exams.result');

    // Competitions
    Route::get('competitions', [\App\Http\Controllers\Student\CompetitionController::class, 'index'])->name('competitions.index');
    Route::post('competitions/{competition}/register', [\App\Http\Controllers\Student\CompetitionController::class, 'register'])->name('competitions.register');
    Route::get('competitions/practice', [\App\Http\Controllers\Student\CompetitionPracticeController::class, 'index'])->name('competitions.practice');
    Route::post('competitions/practice/{paper}/start', [\App\Http\Controllers\Student\CompetitionPracticeController::class, 'start'])->name('competitions.practice.start');
    Route::get('competitions/practice/{paper}/attempt', [\App\Http\Controllers\Student\CompetitionPracticeController::class, 'attempt'])->name('competitions.practice.attempt');
    Route::post('competitions/practice/{paper}/answer', [\App\Http\Controllers\Student\CompetitionPracticeController::class, 'saveAnswer'])->name('competitions.practice.answer');
    Route::post('competitions/practice/{paper}/submit', [\App\Http\Controllers\Student\CompetitionPracticeController::class, 'submit'])->name('competitions.practice.submit');
    Route::get('competitions/practice/{paper}/result', [\App\Http\Controllers\Student\CompetitionPracticeController::class, 'result'])->name('competitions.practice.result');

    // Certificates
    Route::get('certificates', [\App\Http\Controllers\Student\CertificateController::class, 'index'])->name('certificates.index');
    Route::get('certificates/{certificate}/download', [\App\Http\Controllers\Student\CertificateController::class, 'download'])->name('certificates.download');

    // Profile & Notifications
    Route::get('profile', [\App\Http\Controllers\Student\ProfileController::class, 'index'])->name('profile');
    Route::put('profile', [\App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');
    Route::get('notifications', [\App\Http\Controllers\Student\NotificationController::class, 'index'])->name('notifications.index');
});
