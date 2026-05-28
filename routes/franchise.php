<?php

use Illuminate\Support\Facades\Route;

Route::prefix('franchise')->name('franchise.')->middleware(['auth', 'franchise'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Franchise\DashboardController::class, 'index'])->name('dashboard');

    // Help Guide
    Route::get('help', fn() => view('franchise.help'))->name('help');

    // Student management
    Route::resource('students', \App\Http\Controllers\Franchise\StudentController::class);
    Route::post('students/import', [\App\Http\Controllers\Franchise\StudentController::class, 'import'])->name('students.import');
    Route::get('students/import/template', [\App\Http\Controllers\Franchise\StudentController::class, 'importTemplate'])->name('students.import.template');

    // Fees & Payments
    Route::get('fees', [\App\Http\Controllers\Franchise\FeeController::class, 'index'])->name('fees.index');
    Route::get('fees/{fee}', [\App\Http\Controllers\Franchise\FeeController::class, 'show'])->name('fees.show');
    Route::get('fees/{fee}/reminder', [\App\Http\Controllers\Franchise\FeeController::class, 'reminder'])->name('fees.reminder');
    Route::post('payments', [\App\Http\Controllers\Franchise\PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}/receipt', [\App\Http\Controllers\Franchise\PaymentController::class, 'receipt'])->name('payments.receipt');

    // Exams
    Route::resource('exams', \App\Http\Controllers\Franchise\ExamController::class);

    // Certificates
    Route::get('certificates', [\App\Http\Controllers\Franchise\CertificateController::class, 'index'])->name('certificates.index');
    Route::post('certificates', [\App\Http\Controllers\Franchise\CertificateController::class, 'generate'])->name('certificates.generate');
    Route::get('certificates/{certificate}/download', [\App\Http\Controllers\Franchise\CertificateController::class, 'download'])->name('certificates.download');
    Route::get('certificates/{certificate}/pdf', [\App\Http\Controllers\Franchise\CertificateController::class, 'downloadPdf'])->name('certificates.pdf');

    // Promotions
    Route::get('promotions', [\App\Http\Controllers\Franchise\PromotionController::class, 'index'])->name('promotions.index');
    Route::post('promotions/{student}/promote', [\App\Http\Controllers\Franchise\PromotionController::class, 'promote'])->name('promotions.promote');

    // Progress & Reports
    Route::get('reports', [\App\Http\Controllers\Franchise\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [\App\Http\Controllers\Franchise\ReportController::class, 'export'])->name('reports.export');
    Route::get('reports/{student}', [\App\Http\Controllers\Franchise\ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{student}/pdf', [\App\Http\Controllers\Franchise\ReportController::class, 'downloadPdf'])->name('reports.pdf');

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\Franchise\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications', [\App\Http\Controllers\Franchise\NotificationController::class, 'send'])->name('notifications.send');

    // Parent Directory
    Route::get('parents', [\App\Http\Controllers\Franchise\ParentDirectoryController::class, 'index'])->name('parents.index');

    // Competition registrations
    Route::post('competitions/{competition}/register', [\App\Http\Controllers\Franchise\CompetitionRegistrationController::class, 'store'])->name('competitions.register');
    Route::get('competitions', [\App\Http\Controllers\Franchise\CompetitionRegistrationController::class, 'index'])->name('competitions.index');

    // Class Practice Module
    Route::prefix('class-practice')->name('class-practice.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'store'])->name('store');
        Route::get('{session}', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'show'])->name('show');
        Route::get('{session}/project', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'project'])->name('project');
        Route::get('{session}/state', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'state'])->name('state');
        Route::post('{session}/next', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'next'])->name('next');
        Route::post('{session}/reveal', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'reveal'])->name('reveal');
        Route::post('{session}/end', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'end'])->name('end');
        Route::get('{session}/results', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'results'])->name('results');
    });
});
