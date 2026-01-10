<?php

namespace App\Providers;

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
        // Register Observers for Biometric Sync
        \App\Models\Department::observe(\App\Observers\DepartmentObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Shift::observe(\App\Observers\ShiftObserver::class);

        // Automatic biometric sync is handled by AutoSyncBiometric middleware
        // This middleware syncs attendances automatically on each request (throttled to every 10 seconds)
        // No manual commands needed - fingerprints are stored automatically!
    }
}
