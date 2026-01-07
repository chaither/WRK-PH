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
        // Skip auto-sync if disabled via config (useful when using dedicated background process)
        if (!config('zkteco.auto_sync_enabled', true)) {
            return $next($request);
        }
        
        // Skip auto-sync in local/development environment if device IP is not configured or is localhost
        $deviceIp = config('zkteco.device_ip', '192.168.1.100');
        if (app()->environment('local') && ($deviceIp === '127.0.0.1' || $deviceIp === 'localhost' || empty($deviceIp))) {
            return $next($request);
        }
        
        // Sync biometric attendances and users automatically (throttled)
        // This ensures fingerprints and user list are synced without manual commands
        // Skip sync if device is likely unreachable to avoid blocking requests
        try {
            $lastAttendanceSyncKey = 'biometric_auto_sync_last';
            $lastUserSyncKey = 'biometric_auto_user_sync_last';
            $deviceOfflineKey = 'biometric_device_offline';

            $lastAttendanceSync = Cache::get($lastAttendanceSyncKey, 0);
            $lastUserSync = Cache::get($lastUserSyncKey, 0);
            $deviceOffline = Cache::get($deviceOfflineKey, false);

            $now = now()->timestamp;

            $attendanceSyncInterval = 10; // seconds
            $userSyncInterval = 60; // seconds
            $offlineCheckInterval = 300; // 5 minutes - check if device came back online

            // If device was marked offline recently, skip sync to avoid blocking
            // Only retry connection every 5 minutes
            if ($deviceOffline && ($now - Cache::get('biometric_device_offline_time', 0)) < $offlineCheckInterval) {
                return $next($request);
            }

            $zktecoService = app(ZktecoService::class);

            // Auto-sync attendances
            if (($now - $lastAttendanceSync) >= $attendanceSyncInterval) {
                try {
                    $attendanceResult = $zktecoService->syncAttendancesToDTR(false);
                    
                    // If sync succeeded, device is online
                    Cache::forget($deviceOfflineKey);
                    Cache::forget('biometric_device_offline_time');
                    
                    Cache::put($lastAttendanceSyncKey, $now, now()->addMinutes(10));

                    \Log::debug('Auto-synced biometric attendances', [
                        'synced' => $attendanceResult['synced'] ?? 0,
                        'skipped' => $attendanceResult['skipped'] ?? 0,
                    ]);
                } catch (\Exception $syncError) {
                    // If error indicates device is offline, mark it and skip future attempts
                    if (str_contains(strtolower($syncError->getMessage()), 'offline') || 
                        str_contains(strtolower($syncError->getMessage()), 'timeout') ||
                        str_contains(strtolower($syncError->getMessage()), 'connection')) {
                        Cache::put($deviceOfflineKey, true, now()->addMinutes(10));
                        Cache::put('biometric_device_offline_time', $now, now()->addMinutes(10));
                        \Log::info('Biometric device appears offline, skipping auto-sync for 5 minutes');
                    }
                }
            }

            // Auto-sync users to device
            if (($now - $lastUserSync) >= $userSyncInterval && !$deviceOffline) {
                try {
                    $syncedUsers = $zktecoService->syncUsersToDevice();
                    
                    // If sync succeeded, device is online
                    Cache::forget($deviceOfflineKey);
                    Cache::forget('biometric_device_offline_time');
                    
                    Cache::put($lastUserSyncKey, $now, now()->addMinutes(10));

                    \Log::debug('Auto-synced biometric users to device', [
                        'synced_users' => $syncedUsers,
                    ]);
                } catch (\Exception $syncError) {
                    // If error indicates device is offline, mark it and skip future attempts
                    if (str_contains(strtolower($syncError->getMessage()), 'offline') || 
                        str_contains(strtolower($syncError->getMessage()), 'timeout') ||
                        str_contains(strtolower($syncError->getMessage()), 'connection')) {
                        Cache::put($deviceOfflineKey, true, now()->addMinutes(10));
                        Cache::put('biometric_device_offline_time', $now, now()->addMinutes(10));
                        \Log::info('Biometric device appears offline, skipping user sync for 5 minutes');
                    }
                }
            }
        } catch (\Exception $e) {
            // Don't fail the request if sync fails
            \Log::error('Auto-sync biometric error: ' . $e->getMessage());
        }

        return $next($request);
    }
}

