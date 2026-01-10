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
Route::post('/attendance/batch', [AttendanceController::class, 'storeBatch']);
