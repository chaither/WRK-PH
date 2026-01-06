<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZktecoService;

class SyncZktecoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkteco:sync {--users : Sync users to device} {--attendances : Sync attendances from device} {--clear-device : Clear attendances from device after sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data with ZKTeco biometric device (users and/or attendances)';

    protected $zktecoService;

    /**
     * Create a new command instance.
     */
    public function __construct(ZktecoService $zktecoService)
    {
        parent::__construct();
        $this->zktecoService = $zktecoService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $syncUsers = $this->option('users');
        $syncAttendances = $this->option('attendances');
        
        // If no specific option, sync both
        if (!$syncUsers && !$syncAttendances) {
            $syncUsers = true;
            $syncAttendances = true;
        }

        $this->info('Connecting to ZKTeco device...');

        if (!$this->zktecoService->connect()) {
            $this->error('Failed to connect to ZKTeco device. Please check the configuration.');
            return 1;
        }

        if ($syncUsers) {
            $this->info('Syncing users to device...');
            $syncedUsers = $this->zktecoService->syncUsersToDevice();
            if ($syncedUsers !== false) {
                $this->info("Synced {$syncedUsers} users to device.");
            } else {
                $this->error('Failed to sync users.');
            }
        }

        if ($syncAttendances) {
            $this->info('Syncing attendances from device to DTR records...');
            
            $clearDevice = $this->option('clear-device');
            $result = $this->zktecoService->syncAttendancesToDTR($clearDevice);
            
            if (isset($result['error'])) {
                $this->error('Failed to sync attendances: ' . $result['error']);
                return 1;
            }
            
            $this->info("Attendance sync completed. Synced: {$result['synced']}, Skipped: {$result['skipped']}");
            
            // Show skip reasons if available
            if (isset($result['skip_reasons'])) {
                $reasons = $result['skip_reasons'];
                if ($reasons['user_not_found'] > 0) {
                    $this->warn("  - User not found: {$reasons['user_not_found']} records");
                }
                if ($reasons['already_recorded'] > 0) {
                    $this->info("  - Already recorded: {$reasons['already_recorded']} records");
                }
                if ($reasons['all_slots_filled'] > 0) {
                    $this->info("  - All time slots filled: {$reasons['all_slots_filled']} records");
                }
                if ($reasons['error'] > 0) {
                    $this->error("  - Errors: {$reasons['error']} records");
                }
            }
            
            if ($clearDevice) {
                $this->info('Attendances cleared from device.');
            }
        }

        $this->zktecoService->disconnect();

        if ($this->option('clear-device') && $syncAttendances) {
            $this->info('Clearing attendances from device...');
            if ($this->zktecoService->connect() && $this->zktecoService->clearAttendances()) {
                $this->info('Attendances cleared from device.');
            } else {
                $this->error('Failed to clear attendances from device.');
            }
            $this->zktecoService->disconnect();
        }

        return 0;
    }
}