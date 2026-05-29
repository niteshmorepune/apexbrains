<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share site-wide settings (academy name, logo, tagline) with every view.
        View::composer('*', function ($view) {
            static $settings = null;
            if ($settings === null) {
                $defaults = [
                    'app_name'  => 'Apex Brains Academy',
                    'tagline'   => 'Explore your Potential',
                    'logo_path' => null,
                ];
                try {
                    $settings = Storage::disk('local')->exists('settings.json')
                        ? array_merge($defaults, json_decode(Storage::disk('local')->get('settings.json'), true) ?? [])
                        : $defaults;
                } catch (\Throwable $e) {
                    $settings = $defaults;
                }
            }
            $view->with('appSettings', $settings);
        });
    }
}
