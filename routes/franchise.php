<?php

use Illuminate\Support\Facades\Route;

Route::prefix('franchise')->name('franchise.')->group(function () {

    // Public login routes
    Route::get('login', [\App\Http\Controllers\Franchise\FranchiseAuthController::class, 'showLogin'])->name('login')->middleware('guest');
    Route::post('login', [\App\Http\Controllers\Franchise\FranchiseAuthController::class, 'login'])->name('login.post')->middleware('throttle:6,1');
    Route::post('logout', [\App\Http\Controllers\Franchise\FranchiseAuthController::class, 'logout'])->name('logout');

    // All authenticated franchise routes
    Route::middleware(['auth', 'franchise'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Franchise\DashboardController::class, 'index'])->name('dashboard');

        // My Profile
        Route::get('profile', [\App\Http\Controllers\Franchise\FranchiseProfileController::class, 'index'])->name('profile');
        Route::put('profile', [\App\Http\Controllers\Franchise\FranchiseProfileController::class, 'update'])->name('profile.update');

        // Help Guide
        Route::view('help', 'franchise.help')->name('help');

        // Student management — static routes BEFORE resource so they aren't shadowed by students/{student}
        Route::get('students/bulk-import', [\App\Http\Controllers\Franchise\StudentController::class, 'importPage'])->name('students.import.page');
        Route::post('students/import', [\App\Http\Controllers\Franchise\StudentController::class, 'import'])->name('students.import');
        Route::get('students/import/template', [\App\Http\Controllers\Franchise\StudentController::class, 'importTemplate'])->name('students.import.template');
        Route::resource('students', \App\Http\Controllers\Franchise\StudentController::class);

        // Fees & Payments — static routes BEFORE fees/{fee} so they aren't shadowed
        Route::get('fees', [\App\Http\Controllers\Franchise\FeeController::class, 'index'])->name('fees.index');
        Route::get('fees/reminders', [\App\Http\Controllers\Franchise\FeeController::class, 'reminders'])->name('fees.reminders');
        Route::get('fees/record', [\App\Http\Controllers\Franchise\PaymentController::class, 'recordPage'])->name('fees.record');
        Route::get('fees/{fee}', [\App\Http\Controllers\Franchise\FeeController::class, 'show'])->name('fees.show');
        Route::get('fees/{fee}/reminder', [\App\Http\Controllers\Franchise\FeeController::class, 'reminder'])->name('fees.reminder');
        Route::post('payments', [\App\Http\Controllers\Franchise\PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}/receipt', [\App\Http\Controllers\Franchise\PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('payments/{payment}/receipt-pdf', [\App\Http\Controllers\Franchise\PaymentController::class, 'receiptPdf'])->name('payments.receipt.pdf');

        // Exams
        Route::resource('exams', \App\Http\Controllers\Franchise\ExamController::class);

        // Certificates
        Route::get('certificates', [\App\Http\Controllers\Franchise\CertificateController::class, 'index'])->name('certificates.index');
        Route::post('certificates', [\App\Http\Controllers\Franchise\CertificateController::class, 'generate'])->name('certificates.generate');
        Route::get('certificates/{certificate}/download', [\App\Http\Controllers\Franchise\CertificateController::class, 'download'])->name('certificates.download');
        Route::get('certificates/{certificate}/pdf', [\App\Http\Controllers\Franchise\CertificateController::class, 'downloadPdf'])->name('certificates.pdf');
        Route::patch('certificates/{certificate}/revoke', [\App\Http\Controllers\Franchise\CertificateController::class, 'revoke'])->name('certificates.revoke');
        Route::patch('certificates/{certificate}/sent', [\App\Http\Controllers\Franchise\CertificateController::class, 'markSent'])->name('certificates.sent');

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

            // Practice papers (static segment — must precede the {session} routes)
            Route::get('papers', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'papers'])->name('papers');
            Route::post('papers/{paper}/attempt', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'attemptPaper'])->name('papers.attempt');
            Route::get('papers/{paper}/answers', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'paperAnswers'])->name('papers.answers');

            Route::get('{session}', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'show'])->name('show');
            Route::get('{session}/project', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'project'])->name('project');
            Route::get('{session}/state', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'state'])->name('state');
            Route::post('{session}/next', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'next'])->name('next');
            Route::post('{session}/reveal', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'reveal'])->name('reveal');
            Route::post('{session}/end', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'end'])->name('end');
            Route::get('{session}/results', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'results'])->name('results');
            Route::post('{session}/replay', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'replay'])->name('replay');
            Route::post('{session}/again', [\App\Http\Controllers\Franchise\ClassPracticeController::class, 'again'])->name('again');
        });
    });
});
