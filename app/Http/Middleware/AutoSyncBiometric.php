<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\BiometricSyncService;
use App\Services\ZktecoService;
use Illuminate\Support\Facades\Cache;

class AutoSyncBiometric
{
    /**
     * Handle an incoming request.
     * This middleware automatically syncs biometric attendances on each request
     * but throttled to avoid overloading the system
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sync biometric attendances and users automatically (throttled)
        // This ensures fingerprints and user list are synced without manual commands
        try {
            $lastAttendanceSyncKey = 'biometric_auto_sync_last';
            $lastUserSyncKey = 'biometric_auto_user_sync_last';

            $lastAttendanceSync = Cache::get($lastAttendanceSyncKey, 0);
            $lastUserSync = Cache::get($lastUserSyncKey, 0);

            $now = now()->timestamp;

            $attendanceSyncInterval = 10; // seconds
            $userSyncInterval = 60; // seconds

            $zktecoService = app(ZktecoService::class);

            // Auto-sync attendances
            if (($now - $lastAttendanceSync) >= $attendanceSyncInterval) {
                $attendanceResult = $zktecoService->syncAttendancesToDTR(false);

                Cache::put($lastAttendanceSyncKey, $now, now()->addMinutes(10));

                \Log::debug('Auto-synced biometric attendances', [
                    'synced' => $attendanceResult['synced'] ?? 0,
                    'skipped' => $attendanceResult['skipped'] ?? 0,
                ]);
            }

            // Auto-sync users to device
            if (($now - $lastUserSync) >= $userSyncInterval) {
                $syncedUsers = $zktecoService->syncUsersToDevice();

                Cache::put($lastUserSyncKey, $now, now()->addMinutes(10));

                \Log::debug('Auto-synced biometric users to device', [
                    'synced_users' => $syncedUsers,
                ]);
            }
        } catch (\Exception $e) {
            // Don't fail the request if sync fails
            \Log::error('Auto-sync biometric error: ' . $e->getMessage());
        }

        return $next($request);
    }
}

