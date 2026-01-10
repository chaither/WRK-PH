<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-employee-sync/{id}', function($id) {
    $user = \App\Models\User::find($id);
    
    if (!$user) {
        return response()->json(['error' => 'User not found']);
    }
    
    $syncService = new \App\Services\BiometricSyncService();
    
    try {
        $syncService->syncEmployee($user);
        return response()->json([
            'success' => true,
            'message' => "Manually synced employee {$user->id} to biometric app",
            'user' => $user
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\SyncDataController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;

Route::get('/sync/updates', [SyncDataController::class, 'getUpdates']);
Route::post('/attendance/batch', [AttendanceController::class, 'storeBatch']);

// Sync Routes (Incoming from Biometric App)
// Note: api.php prefix is /api
Route::post('/departments', [DepartmentController::class, 'store']);
Route::put('/departments/{department}', [DepartmentController::class, 'update']);
Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);
Route::post('/employees', [EmployeeController::class, 'store']);
Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
