<?php

namespace App\Services;

use App\Services\ZktecoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BiometricSyncService
{
    protected $zktecoService;
    protected $isRunning = false;
    protected $syncInterval = 10; // seconds

    public function __construct(ZktecoService $zktecoService)
    {
        $this->zktecoService = $zktecoService;
    }

    /**
     * Start automatic background sync
     * This will run continuously and sync attendances every few seconds
     */
    public function startAutoSync()
    {
        // Check if sync is already running
        $lockKey = 'biometric_sync_running';
        if (Cache::has($lockKey)) {
            return;
        }

        // Set lock to prevent multiple instances
        Cache::put($lockKey, true, now()->addMinutes(5));

        // Start sync in background
        $this->runContinuousSync();
    }

    /**
     * Run continuous sync in background
     */
    protected function runContinuousSync()
    {
        // Use exec to run the command in background (Windows compatible)
        $command = sprintf(
            'php "%s" artisan zkteco:sync-continuous --interval=%d > nul 2>&1',
            base_path(),
            $this->syncInterval
        );

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: Start process in background
            pclose(popen("start /B " . $command, "r"));
        } else {
            // Linux/Unix: Run in background
            exec($command . " &");
        }

        Log::info('Automatic biometric sync started in background');
    }

    /**
     * Sync attendances immediately (called on each request)
     */
    public function syncIfNeeded()
    {
        // Only sync if last sync was more than syncInterval seconds ago
        $lastSyncKey = 'biometric_last_sync';
        $lastSync = Cache::get($lastSyncKey, 0);
        $now = now()->timestamp;

        if (($now - $lastSync) >= $this->syncInterval) {
            try {
                $result = $this->zktecoService->syncAttendancesToDTR(false);
                Cache::put($lastSyncKey, $now, now()->addMinutes(10));
                
                Log::debug('Biometric sync triggered', [
                    'synced' => $result['synced'] ?? 0,
                    'skipped' => $result['skipped'] ?? 0
                ]);
            } catch (\Exception $e) {
                Log::error('Error during automatic biometric sync: ' . $e->getMessage());
            }
        }
    }
}

