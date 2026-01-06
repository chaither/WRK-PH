<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZktecoService;

class ContinuousSyncAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkteco:sync-continuous {--interval=10 : Sync interval in seconds (default: 10)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously sync attendances from biometric device every few seconds (for real-time sync)';

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
        $interval = (int) $this->option('interval');
        
        if ($interval < 5) {
            $this->warn('Interval too short, minimum is 5 seconds. Using 5 seconds.');
            $interval = 5;
        }

        $this->info("Starting continuous attendance sync (every {$interval} seconds)...");
        $this->info('Press Ctrl+C to stop.');

        while (true) {
            try {
                $this->line('');
                $this->info('[' . now()->format('Y-m-d H:i:s') . '] Syncing attendances...');
                
                $result = $this->zktecoService->syncAttendancesToDTR(false);
                
                if (isset($result['error'])) {
                    $this->error('Sync failed: ' . $result['error']);
                } else {
                    $this->info("Synced: {$result['synced']}, Skipped: {$result['skipped']}");
                    
                    if (isset($result['skip_reasons'])) {
                        $reasons = $result['skip_reasons'];
                        if ($reasons['user_not_found'] > 0) {
                            $this->warn("  - User not found: {$reasons['user_not_found']}");
                        }
                        if ($reasons['already_recorded'] > 0) {
                            $this->line("  - Already recorded: {$reasons['already_recorded']}");
                        }
                        if ($reasons['all_slots_filled'] > 0) {
                            $this->line("  - All slots filled: {$reasons['all_slots_filled']}");
                        }
                    }
                }
                
                // Sleep for the specified interval
                sleep($interval);
            } catch (\Exception $e) {
                $this->error('Error during sync: ' . $e->getMessage());
                sleep($interval);
            }
        }
    }
}

