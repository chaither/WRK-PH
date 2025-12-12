<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Holiday;
use App\Services\PhilippineHolidayService;

class SeedPhilippineHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:seed-philippines {year? : The year to seed holidays for (defaults to current year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Philippine holidays (regular and special non-working) for a given year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? date('Y');
        
        if (!is_numeric($year) || $year < 2000 || $year > 2100) {
            $this->error('Invalid year. Please provide a year between 2000 and 2100.');
            return 1;
        }

        $this->info("Seeding Philippine holidays for year {$year}...");

        $service = new PhilippineHolidayService();
        $holidays = $service->getHolidaysForYear((int)$year);

        $created = 0;
        $skipped = 0;

        foreach ($holidays as $holidayData) {
            try {
                Holiday::firstOrCreate(
                    ['date' => $holidayData['date']],
                    [
                        'name' => $holidayData['name'],
                        'type' => $holidayData['type'],
                        'rate_multiplier' => $holidayData['rate_multiplier'],
                    ]
                );
                $created++;
                $this->line("✓ Created: {$holidayData['name']} ({$holidayData['date']})");
            } catch (\Exception $e) {
                $skipped++;
                $this->warn("⊘ Skipped: {$holidayData['name']} ({$holidayData['date']}) - Already exists or error occurred");
            }
        }

        $this->info("\nCompleted! Created: {$created}, Skipped: {$skipped}");
        return 0;
    }
}