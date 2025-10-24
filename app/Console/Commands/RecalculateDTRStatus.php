<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DTRRecord;

class RecalculateDTRStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dtr:recalculate {--id= : Specify a DTR record ID to recalculate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates late status and late minutes for DTR records. Use --id to specify a single record.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->option('id');

        if ($id) {
            $records = DTRRecord::where('id', $id)->get();
            if ($records->isEmpty()) {
                $this->error("DTR Record with ID {$id} not found.");
                return;
            }
            $this->info("Recalculating DTR status for record ID: {$id}");
        } else {
            $records = DTRRecord::all();
            $this->info('Recalculating DTR status for all records...');
        }

        foreach ($records as $record) {
            $record->recalculateLateStatus();
            $record->save();
            $this->info("Record ID {$record->id} updated. New late_minutes: {$record->late_minutes}");
        }

        $this->info('DTR Status recalculation complete.');
    }
}
