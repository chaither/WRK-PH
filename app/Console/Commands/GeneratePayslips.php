<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayrollService;
use Carbon\Carbon;
use App\Models\PayrollSchedule;

class GeneratePayslips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates payslips for the current pay period.';

    /**
     * The PayrollService instance.
     *
     * @var \App\Services\PayrollService
     */
    protected $payrollService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\PayrollService $payrollService
     * @return void
     */
    public function __construct(PayrollService $payrollService)
    {
        parent::__construct();
        $this->payrollService = $payrollService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $payrollSchedules = PayrollSchedule::all();

        foreach ($payrollSchedules as $schedule) {
            foreach ($schedule->generation_days as $day) {
                $shouldGenerate = false;
                $payPeriodStart = null;
                $payPeriodEnd = null;
                $payPeriodType = '';

                if ($day === 'last_day') {
                    $shouldGenerate = $today->isLastDayOfMonth();
                } else {
                    $shouldGenerate = ($today->day == (int)$day);
                }

                if ($shouldGenerate) {
                    if ($schedule->pay_period_type === 'semi-monthly') {
                        if ($day == 15) {
                            $payPeriodStart = $today->copy()->startOfMonth();
                            $payPeriodEnd = $today->copy()->day(15);
                            $payPeriodType = 'semi-monthly (1st-15th)';
                        } elseif ($day === 'last_day') {
                            $payPeriodStart = $today->copy()->day(16);
                            $payPeriodEnd = $today->copy()->endOfMonth();
                            $payPeriodType = 'semi-monthly (16th-end)';
                        }
                    } elseif ($schedule->pay_period_type === 'monthly') {
                        if ($day === 'last_day') {
                            $payPeriodStart = $today->copy()->startOfMonth();
                            $payPeriodEnd = $today->copy()->endOfMonth();
                            $payPeriodType = 'monthly';
                        }
                    }

                    if ($payPeriodStart && $payPeriodEnd) {
                        $this->info("Generating payslips for {$payPeriodType} period: {$payPeriodStart->toDateString()} to {$payPeriodEnd->toDateString()}");
                        $this->payrollService->generatePayslipsForPeriod($payPeriodStart, $payPeriodEnd, $schedule->pay_period_type);
                        $this->info('Payslips generated successfully!');
                    }
                }
            }
        }
        $this->info('Payroll generation check complete.');
    }
}
