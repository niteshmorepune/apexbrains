<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // Login (unauthenticated)
    Route::get('login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'showLogin'])->name('login')->middleware('guest');
    Route::post('login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'login'])->name('login.post')->middleware('throttle:6,1');
    Route::post('logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])->name('logout');

    // All authenticated admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Franchise management
        Route::resource('franchises', \App\Http\Controllers\Admin\FranchiseController::class);
        Route::post('franchises/{franchise}/approve', [\App\Http\Controllers\Admin\FranchiseController::class, 'approve'])->name('franchises.approve');
        Route::post('franchises/{franchise}/suspend', [\App\Http\Controllers\Admin\FranchiseController::class, 'suspend'])->name('franchises.suspend');

        // Level management
        Route::resource('levels', \App\Http\Controllers\Admin\LevelController::class);

        // Question Bank — audio/generate must come BEFORE resource to avoid {question} param conflict
        Route::get('questions/audio/generate', [\App\Http\Controllers\Admin\AudioQuestionController::class, 'index'])->name('questions.audio');
        Route::post('questions/audio/generate', [\App\Http\Controllers\Admin\AudioQuestionController::class, 'generate'])->name('questions.audio.generate');
        Route::resource('questions', \App\Http\Controllers\Admin\QuestionBankController::class);
        Route::post('questions/{question}/approve', [\App\Http\Controllers\Admin\QuestionBankController::class, 'approve'])->name('questions.approve');
        Route::post('questions/{question}/reject', [\App\Http\Controllers\Admin\QuestionBankController::class, 'reject'])->name('questions.reject');

        // PDF Upload & OCR
        Route::get('pdf-uploads', [\App\Http\Controllers\Admin\PdfUploadController::class, 'index'])->name('pdf-uploads.index');
        Route::post('pdf-uploads', [\App\Http\Controllers\Admin\PdfUploadController::class, 'store'])->name('pdf-uploads.store');
        Route::get('pdf-uploads/{pdfUpload}', [\App\Http\Controllers\Admin\PdfUploadController::class, 'show'])->name('pdf-uploads.show');

        // Competition management
        Route::resource('competitions', \App\Http\Controllers\Admin\CompetitionController::class);
        Route::resource('competition-papers', \App\Http\Controllers\Admin\CompetitionPaperController::class);

        // Analytics & reports
        Route::get('revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])->name('revenue');
        Route::get('leaderboard', [\App\Http\Controllers\Admin\LeaderboardController::class, 'index'])->name('leaderboard');
        Route::get('commissions', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions.index');
        Route::post('commissions/calculate', [\App\Http\Controllers\Admin\CommissionController::class, 'calculate'])->name('commissions.calculate');
        Route::post('commissions/{commission}/mark-paid', [\App\Http\Controllers\Admin\CommissionController::class, 'markPaid'])->name('commissions.mark-paid');

        // Settings & Audit
        Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings');
        Route::post('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('audit-log', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-log');

        // Help Guide
        Route::get('help', fn() => view('admin.help'))->name('help');
    });
});
