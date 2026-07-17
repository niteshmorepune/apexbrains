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

        // Audio question generator (Regular bank only) — must come before the resource below to avoid {question} conflict
        Route::get('questions/audio/generate', [\App\Http\Controllers\Admin\AudioQuestionController::class, 'index'])->name('questions.audio');
        Route::post('questions/audio/generate', [\App\Http\Controllers\Admin\AudioQuestionController::class, 'generate'])->name('questions.audio.generate');

        // Regular Question Bank (Regular Practice + Class Practice) — Category -> Type taxonomy
        Route::get('regular-questions/import', [\App\Http\Controllers\Admin\RegularQuestionImportController::class, 'index'])->name('regular-questions.import');
        Route::post('regular-questions/import', [\App\Http\Controllers\Admin\RegularQuestionImportController::class, 'store'])->name('regular-questions.import.store');
        Route::get('regular-questions/import/template', [\App\Http\Controllers\Admin\RegularQuestionImportController::class, 'template'])->name('regular-questions.import.template');
        Route::get('regular-questions/taxonomy', [\App\Http\Controllers\Admin\RegularQuestionTaxonomyController::class, 'index'])->name('regular-questions.taxonomy');
        Route::post('regular-questions/taxonomy/categories', [\App\Http\Controllers\Admin\RegularQuestionTaxonomyController::class, 'storeCategory'])->name('regular-questions.taxonomy.categories.store');
        Route::post('regular-questions/taxonomy/categories/{category}/types', [\App\Http\Controllers\Admin\RegularQuestionTaxonomyController::class, 'storeType'])->name('regular-questions.taxonomy.types.store');
        Route::delete('regular-questions/taxonomy/types/{type}', [\App\Http\Controllers\Admin\RegularQuestionTaxonomyController::class, 'destroyType'])->name('regular-questions.taxonomy.types.destroy');
        Route::resource('regular-questions', \App\Http\Controllers\Admin\RegularQuestionBankController::class)->parameters(['regular-questions' => 'question']);
        Route::post('regular-questions/{question}/approve', [\App\Http\Controllers\Admin\RegularQuestionBankController::class, 'approve'])->name('regular-questions.approve');
        Route::post('regular-questions/{question}/reject', [\App\Http\Controllers\Admin\RegularQuestionBankController::class, 'reject'])->name('regular-questions.reject');

        // Competition Question Bank (Competition Practice only) — Category -> Type taxonomy
        Route::get('competition-questions/import', [\App\Http\Controllers\Admin\CompetitionQuestionImportController::class, 'index'])->name('competition-questions.import');
        Route::post('competition-questions/import', [\App\Http\Controllers\Admin\CompetitionQuestionImportController::class, 'store'])->name('competition-questions.import.store');
        Route::get('competition-questions/import/template', [\App\Http\Controllers\Admin\CompetitionQuestionImportController::class, 'template'])->name('competition-questions.import.template');
        Route::get('competition-questions/taxonomy', [\App\Http\Controllers\Admin\CompetitionQuestionTaxonomyController::class, 'index'])->name('competition-questions.taxonomy');
        Route::post('competition-questions/taxonomy/categories', [\App\Http\Controllers\Admin\CompetitionQuestionTaxonomyController::class, 'storeCategory'])->name('competition-questions.taxonomy.categories.store');
        Route::post('competition-questions/taxonomy/categories/{category}/types', [\App\Http\Controllers\Admin\CompetitionQuestionTaxonomyController::class, 'storeType'])->name('competition-questions.taxonomy.types.store');
        Route::delete('competition-questions/taxonomy/types/{type}', [\App\Http\Controllers\Admin\CompetitionQuestionTaxonomyController::class, 'destroyType'])->name('competition-questions.taxonomy.types.destroy');
        Route::resource('competition-questions', \App\Http\Controllers\Admin\CompetitionQuestionBankController::class)->parameters(['competition-questions' => 'question']);
        Route::post('competition-questions/{question}/approve', [\App\Http\Controllers\Admin\CompetitionQuestionBankController::class, 'approve'])->name('competition-questions.approve');
        Route::post('competition-questions/{question}/reject', [\App\Http\Controllers\Admin\CompetitionQuestionBankController::class, 'reject'])->name('competition-questions.reject');

        // Level access configuration — sourced from the client's two practice-type Excels
        Route::get('regular-practice-access', [\App\Http\Controllers\Admin\RegularPracticeAccessController::class, 'index'])->name('regular-practice-access.index');
        Route::post('regular-practice-access/import', [\App\Http\Controllers\Admin\RegularPracticeAccessController::class, 'store'])->name('regular-practice-access.store');
        Route::get('regular-practice-access/template', [\App\Http\Controllers\Admin\RegularPracticeAccessController::class, 'template'])->name('regular-practice-access.template');

        Route::get('competition-practice-config', [\App\Http\Controllers\Admin\CompetitionPracticeConfigController::class, 'index'])->name('competition-practice-config.index');
        Route::post('competition-practice-config/import', [\App\Http\Controllers\Admin\CompetitionPracticeConfigController::class, 'store'])->name('competition-practice-config.store');
        Route::get('competition-practice-config/template', [\App\Http\Controllers\Admin\CompetitionPracticeConfigController::class, 'template'])->name('competition-practice-config.template');
        Route::patch('competition-practice-levels/{level}', [\App\Http\Controllers\Admin\CompetitionPracticeConfigController::class, 'updateDuration'])->name('competition-practice-levels.update');

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
        Route::get('level-up-exam-papers/template', [\App\Http\Controllers\Admin\LevelUpExamPaperController::class, 'template'])->name('level-up-exam-papers.template');
        Route::post('exams/{exam}/papers', [\App\Http\Controllers\Admin\LevelUpExamPaperController::class, 'store'])->name('exams.papers.store');
        Route::delete('exams/{exam}/papers/{paper}', [\App\Http\Controllers\Admin\LevelUpExamPaperController::class, 'destroy'])->name('exams.papers.destroy');

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
