<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ZktecoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ZKTeco Biometric API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/biometric/device-info', [ZktecoController::class, 'deviceInfo']);
    Route::get('/biometric/users', [ZktecoController::class, 'getUsers']);
    Route::get('/biometric/attendances', [ZktecoController::class, 'getAttendances']);
    Route::delete('/biometric/attendances', [ZktecoController::class, 'clearAttendances']);
    Route::post('/biometric/users', [ZktecoController::class, 'setUser']);
    Route::delete('/biometric/users', [ZktecoController::class, 'deleteUser']);
    Route::get('/biometric/time', [ZktecoController::class, 'getTime']);
    Route::post('/biometric/time', [ZktecoController::class, 'setTime']);
    Route::post('/biometric/sync-users', [ZktecoController::class, 'syncUsers']);
    Route::post('/biometric/sync-attendances', [ZktecoController::class, 'syncAttendances']);
});