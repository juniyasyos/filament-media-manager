<?php

namespace Juniyasyos\FilamentMediaManager;

use Illuminate\Support\ServiceProvider;
use Juniyasyos\FilamentMediaManager\Services\FilamentMediaManagerServices;
use Juniyasyos\FilamentMediaManager\Console\FilamentMediaManagerInstall;

class FilamentMediaManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Console Command
        $this->commands([
            FilamentMediaManagerInstall::class,
        ]);

        // Merge Configuration File
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-media-manager.php', 'filament-media-manager');

        // Bind Service
        $this->app->singleton('filament-media-manager', function () {
            return new FilamentMediaManagerServices();
        });
    }

    public function boot(): void
    {
        // Publish Config File
        $this->publishes([
            __DIR__ . '/../config/filament-media-manager.php' => config_path('filament-media-manager.php'),
        ], 'filament-media-manager-config');

        // Load and Publish Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-media-manager');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-media-manager'),
        ], 'filament-media-manager-views');

        // Load and Publish Language Files
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-media-manager');
        $this->publishes([
            __DIR__ . '/../resources/lang' => base_path('lang/vendor/filament-media-manager'),
        ], 'filament-media-manager-lang');

        // Load Routes If API is Enabled
        if (config('filament-media-manager.api.active')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }
    }
}
