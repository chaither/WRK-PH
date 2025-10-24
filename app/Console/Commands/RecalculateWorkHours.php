<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DTRRecord;

class RecalculateWorkHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dtr:recalculate-work-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates work hours and overtime hours for all DTR records.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recalculation of work hours and overtime hours...');

        DTRRecord::chunk(100, function ($records) {
            foreach ($records as $record) {
                $record->recalculateAllHours(); // This method will be created in the DTRRecord model
                $record->save();
            }
        });

        $this->info('Work hours and overtime hours recalculated successfully!');
    }
}
