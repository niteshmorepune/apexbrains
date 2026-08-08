<?php

use App\Console\Commands\NotifyStartingToday;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ExternalStudentMiddleware;
use App\Http\Middleware\FranchiseMiddleware;
use App\Http\Middleware\InternalStudentMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            Route::middleware('web')
                ->group(base_path('routes/franchise.php'));
            Route::middleware('web')
                ->group(base_path('routes/student.php'));
            Route::middleware('web')
                ->group(base_path('routes/external.php'));
        },
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command(NotifyStartingToday::class)->dailyAt('07:00')->timezone('Asia/Kolkata');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'franchise' => FranchiseMiddleware::class,
            'internal.student' => InternalStudentMiddleware::class,
            'external.student' => ExternalStudentMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
