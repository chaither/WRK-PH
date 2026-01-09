<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude API routes from CSRF verification (for biometric-app sync)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        
        // Automatically sync biometric attendances on each request (throttled)
        // This ensures fingerprints are stored immediately without manual commands
        // Middleware removed as logic moved to biometric-app
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
