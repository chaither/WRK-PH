<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Biometric App Service Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration is used by the HRIS system to communicate with
    | the Biometric App for syncing departments and employees.
    |
    */

    'biometric_app' => [
        'url' => env('BIOMETRIC_APP_URL', 'http://localhost:8081'),
    ],

    'hris' => [
        'url' => env('HRIS_URL', 'http://localhost:8000'),
    ],
];
