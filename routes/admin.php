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
        Route::get('export', [\App\Http\Controllers\Admin\DashboardController::class, 'export'])->name('dashboard.export');

        // Franchise management — static routes BEFORE resource so {franchise} doesn't capture them
        Route::get('franchises/performance', [\App\Http\Controllers\Admin\FranchiseController::class, 'performance'])->name('franchises.performance');
        Route::get('franchises/approval-queue', [\App\Http\Controllers\Admin\FranchiseController::class, 'approvalQueue'])->name('franchises.approval-queue');
        Route::resource('franchises', \App\Http\Controllers\Admin\FranchiseController::class);
        Route::post('franchises/{franchise}/documents', [\App\Http\Controllers\Admin\FranchiseController::class, 'uploadDocuments'])->name('franchises.documents');
        Route::post('franchises/{franchise}/approve', [\App\Http\Controllers\Admin\FranchiseController::class, 'approve'])->name('franchises.approve');
        Route::post('franchises/{franchise}/suspend', [\App\Http\Controllers\Admin\FranchiseController::class, 'suspend'])->name('franchises.suspend');
        Route::post('franchises/{franchise}/reject', [\App\Http\Controllers\Admin\FranchiseController::class, 'reject'])->name('franchises.reject');

        // Level management
        Route::resource('levels', \App\Http\Controllers\Admin\LevelController::class);

        // Question Bank — audio/generate must come BEFORE resource to avoid {question} param conflict
        Route::get('questions/audio/generate', [\App\Http\Controllers\Admin\AudioQuestionController::class, 'index'])->name('questions.audio');
        Route::post('questions/audio/generate', [\App\Http\Controllers\Admin\AudioQuestionController::class, 'generate'])->name('questions.audio.generate');
        Route::get('questions/import', [\App\Http\Controllers\Admin\QuestionImportController::class, 'index'])->name('questions.import');
        Route::post('questions/import', [\App\Http\Controllers\Admin\QuestionImportController::class, 'store'])->name('questions.import.store');
        Route::get('questions/import/template', [\App\Http\Controllers\Admin\QuestionImportController::class, 'template'])->name('questions.import.template');
        Route::resource('questions', \App\Http\Controllers\Admin\QuestionBankController::class);
        Route::post('questions/{question}/approve', [\App\Http\Controllers\Admin\QuestionBankController::class, 'approve'])->name('questions.approve');
        Route::post('questions/{question}/reject', [\App\Http\Controllers\Admin\QuestionBankController::class, 'reject'])->name('questions.reject');

        // Competition management
        // Per-competition question papers (CSV upload, level-wise, deletable) —
        // static template route before the resource to avoid shadowing.
        Route::get('competition-question-papers/template', [\App\Http\Controllers\Admin\CompetitionQuestionPaperController::class, 'template'])->name('competition-question-papers.template');
        Route::get('competitions/{competition}/papers/create', [\App\Http\Controllers\Admin\CompetitionQuestionPaperController::class, 'create'])->name('competitions.papers.create');
        Route::post('competitions/{competition}/papers', [\App\Http\Controllers\Admin\CompetitionQuestionPaperController::class, 'store'])->name('competitions.papers.store');
        Route::delete('competitions/{competition}/papers/{paper}', [\App\Http\Controllers\Admin\CompetitionQuestionPaperController::class, 'destroy'])->name('competitions.papers.destroy');
        Route::resource('competitions', \App\Http\Controllers\Admin\CompetitionController::class);

        // Exams (authored centrally by Admin, global to all franchises)
        Route::resource('exams', \App\Http\Controllers\Admin\ExamController::class);

        // Analytics & reports
        Route::get('revenue', [\App\Http\Controllers\Admin\RevenueController::class, 'index'])->name('revenue');
        Route::get('revenue/export-pdf', [\App\Http\Controllers\Admin\RevenueController::class, 'exportPdf'])->name('revenue.export-pdf');
        Route::get('leaderboard', [\App\Http\Controllers\Admin\LeaderboardController::class, 'index'])->name('leaderboard');

        // Resource Library
        Route::get('resources', [\App\Http\Controllers\Admin\ResourceFileController::class, 'index'])->name('resources.index');
        Route::post('resources', [\App\Http\Controllers\Admin\ResourceFileController::class, 'store'])->name('resources.store');
        Route::get('resources/{resource}/download', [\App\Http\Controllers\Admin\ResourceFileController::class, 'download'])->name('resources.download');
        Route::delete('resources/{resource}', [\App\Http\Controllers\Admin\ResourceFileController::class, 'destroy'])->name('resources.destroy');

        // Profile
        Route::get('profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'index'])->name('profile');
        Route::put('profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');

        // Settings & Audit
        Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings');
        Route::post('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('audit-log', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-log');
        Route::get('audit-log/export', [\App\Http\Controllers\Admin\AuditLogController::class, 'export'])->name('audit-log.export');

        // Help Guide — use Route::view so route:cache works (no Closure)
        Route::view('help', 'admin.help')->name('help');
    });
});
