<?php

namespace App\Services;

use App\Models\User;
use App\Models\Holiday;
use App\Models\Payslip;
use App\Models\PayPeriod;
use Carbon\Carbon;
use App\Models\GovernmentContribution;
use Illuminate\Support\Facades\Log;

class PayrollService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Generates payslips for all eligible employees for a given pay period.
     *
     * @param Carbon $payPeriodStart
     * @param Carbon $payPeriodEnd
     * @return void
     */
    public function generatePayslipsForPeriod(Carbon $payPeriodStart, Carbon $payPeriodEnd, string $payScheduleFilter = null, ?array $departmentIds = null): void
    {
        $payPeriod = PayPeriod::firstOrCreate(
            ['start_date' => $payPeriodStart, 'end_date' => $payPeriodEnd],
            ['status' => 'processing', 'pay_period_type' => $payScheduleFilter ?? 'semi-monthly']
        );

        $employeesQuery = User::where('role', 'employee');
        if ($payScheduleFilter) {
            $employeesQuery->where('pay_schedule', $payScheduleFilter);
        }
        if ($departmentIds !== null) {
            $employeesQuery->whereIn('department_id', $departmentIds);
        }

        $employees = $employeesQuery->get();
        $holidays = Holiday::whereBetween('date', [$payPeriodStart, $payPeriodEnd])->get()->keyBy('date');
        $governmentContributions = GovernmentContribution::all()->groupBy('type');

        foreach ($employees as $employee) {
            $payPeriodDates = $this->getPayPeriodDates($employee->pay_schedule, $payPeriodStart, $payPeriodEnd, $payScheduleFilter);

            foreach ($payPeriodDates as $period) {
                $this->generateEmployeePayslip($employee, $payPeriod, $period['start'], $period['end'], $holidays, $governmentContributions);
            }
        }
    }

    /**
     * Determines the actual pay period start and end dates based on the employee's pay schedule.
     *
     * @param string $paySchedule
     * @param Carbon $monthStart
     * @param Carbon $monthEnd
     * @return array
     */
    public function getPayPeriodDates(string $paySchedule, Carbon $monthStart, Carbon $monthEnd, string $payScheduleFilter = null): array
    {
        $periods = [];

        if ($paySchedule === 'semi-monthly') {
            // First half of the month (1st to 15th)
            $firstHalfEnd = $monthStart->copy()->day(15);

            if ((!$payScheduleFilter || ($payScheduleFilter === 'semi-monthly' && $monthEnd->day < 16)) && $firstHalfEnd->gte($monthStart)) { // Ensure the first half is within the requested month
                $periods[] = [
                    'start' => $monthStart->copy(),
                    'end' => $firstHalfEnd,
                ];
            }

            // Second half of the month (16th to end of month)
            $secondHalfStart = $monthStart->copy()->day(16);
            $secondHalfEnd = $monthEnd->copy();

            if ((!$payScheduleFilter || ($payScheduleFilter === 'semi-monthly' && $monthStart->day >= 16)) && $secondHalfStart->lte($monthEnd)) { // Ensure the second half is within the requested month
                $periods[] = [
                    'start' => $secondHalfStart,
                    'end' => $secondHalfEnd,
                ];
            }
        } else {
            // Default to monthly if not semi-monthly or other specific schedules
            if (!$payScheduleFilter || $payScheduleFilter === $paySchedule) {
                $periods[] = [
                    'start' => $monthStart->copy(),
                    'end' => $monthEnd->copy(),
                ];
            }
        }

        return $periods;
    }

    /**
     * Generates a single payslip for an employee for a given pay period.
     *
     * @param User $employee
     * @param PayPeriod $payPeriod
     * @param Carbon $payPeriodStart
     * @param Carbon $payPeriodEnd
     * @param \Illuminate\Support\Collection $holidays
     * @return void
     */
    private function generateEmployeePayslip(User $employee, PayPeriod $payPeriod, Carbon $payPeriodStart, Carbon $payPeriodEnd, \Illuminate\Support\Collection $holidays, \Illuminate\Support\Collection $governmentContributions): void
    {
        // Determine the actual days in the pay period
        $currentDate = $payPeriodStart->copy();
        $workingDays = $this->normalizeDaysArray($employee->working_days, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
        $restDays = $this->normalizeDaysArray($employee->rest_days);
        $totalWorkingDaysInPeriod = $this->getActualWorkingDaysInMonth($workingDays, $restDays, $payPeriodStart, $payPeriodEnd);
        $totalHolidayWorkingDays = [
            'regular' => ['count' => 0, 'multiplier' => 1.00],
            'special_non_working' => ['count' => 0, 'multiplier' => 1.00],
        ];

        $workingDays = $this->normalizeDaysArray($employee->working_days, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
        $restDays = $this->normalizeDaysArray($employee->rest_days);
        $dtrRecords = $employee->dtrRecords()
            ->whereBetween('date', [$payPeriodStart, $payPeriodEnd])
            ->get();
        $presentDays = 0;
        $totalActualWorkHours = 0;

        while ($currentDate->lte($payPeriodEnd)) {
            $dayName = $currentDate->format('l'); // e.g., 'Monday'
            $isWorkingDay = in_array($dayName, $workingDays) && !in_array($dayName, $restDays);
            $isHoliday = $holidays->has($currentDate->toDateString());
            // Find DTR for the current day using a more robust comparison
            $dtrForDay = $dtrRecords->first(function ($dtr) use ($currentDate) {
                return $dtr->date->toDateString() === $currentDate->toDateString();
            });

            Log::info('Processing date: ' . $currentDate->toDateString() . ', isWorkingDay: ' . ($isWorkingDay ? 'true' : 'false') . ', dtrForDay exists: ' . ($dtrForDay ? 'true' : 'false') . ', time_in: ' . ($dtrForDay && $dtrForDay->time_in ? $dtrForDay->time_in->toDateTimeString() : 'NULL') . ', time_in_2: ' . ($dtrForDay && $dtrForDay->time_in_2 ? $dtrForDay->time_in_2->toDateTimeString() : 'NULL'));

            if ($isWorkingDay) {
                if ($dtrForDay && ($dtrForDay->time_in || $dtrForDay->time_in_2)) {
                    $presentDays++;
                    $totalActualWorkHours += $dtrForDay->work_hours; // Accumulate actual work hours

                    if ($isHoliday) {
                        $holiday = $holidays->get($currentDate->toDateString());
                        if (isset($totalHolidayWorkingDays[$holiday->type])) {
                            $totalHolidayWorkingDays[$holiday->type]['count']++;
                            $totalHolidayWorkingDays[$holiday->type]['multiplier'] = $holiday->rate_multiplier; // Store the multiplier
                        }
                    }
                } else if ($isHoliday) {
                    // If it's a holiday and no DTR, consider it a present day if the holiday is paid
                    $holiday = $holidays->get($currentDate->toDateString());
                    if ($holiday && $holiday->is_paid) {
                        $presentDays++;
                        if (isset($totalHolidayWorkingDays[$holiday->type])) {
                            $totalHolidayWorkingDays[$holiday->type]['count']++;
                            $totalHolidayWorkingDays[$holiday->type]['multiplier'] = $holiday->rate_multiplier; // Store the multiplier
                        }
                    }
                }
            }
            $currentDate->addDay();
        }

        // Get employee's effective monthly salary
        $effectiveMonthlySalary = $employee->basic_salary;
        $daysInMonth = $payPeriodStart->daysInMonth;
        $workingHoursPerDay = 8; // Assuming 8 working hours per day

        $actualWorkingDaysInPeriod = $this->getActualWorkingDaysInMonth($workingDays, $restDays, $payPeriodStart, $payPeriodEnd);

        // Adjust effective salary based on pay schedule for the current period
        $effectivePeriodSalary = $effectiveMonthlySalary;
        if ($employee->pay_schedule === 'semi-monthly') {
            $daysInCurrentPayPeriod = $this->getDaysInPayPeriod($payPeriodStart, $payPeriodEnd);
            $effectivePeriodSalary = ($effectiveMonthlySalary / $daysInMonth) * $daysInCurrentPayPeriod;
        }

        $dailyRate = ($actualWorkingDaysInPeriod > 0) ? $effectivePeriodSalary / $actualWorkingDaysInPeriod : 0;
        $hourlyRate = ($dailyRate > 0) ? $dailyRate / $workingHoursPerDay : 0;

        $grossPay = ($totalActualWorkHours * $hourlyRate) +
                    ($totalHolidayWorkingDays['regular']['count'] * $dailyRate * $totalHolidayWorkingDays['regular']['multiplier']) + // Regular Holiday
                    ($totalHolidayWorkingDays['special_non_working']['count'] * $dailyRate * $totalHolidayWorkingDays['special_non_working']['multiplier']); // Special Non-Working Holiday

        // Calculate Government Contributions
        $sssDeduction = $this->calculateContribution('sss', $effectivePeriodSalary, $governmentContributions, $employee);
        $philhealthDeduction = $this->calculateContribution('philhealth', $effectivePeriodSalary, $governmentContributions, $employee);
        $pagibigDeduction = $this->calculateContribution('pagibig', $effectivePeriodSalary, $governmentContributions, $employee);

        // Total deductions from government contributions
        $governmentDeductions = $sssDeduction + $philhealthDeduction + $pagibigDeduction;

        // Deductions (simplified for now)
        $deductions = $governmentDeductions; // Start with government deductions

        $netPay = $grossPay - $deductions;

        Payslip::updateOrCreate(
            [
                'user_id' => $employee->id,
                'pay_period_id' => $payPeriod->id,
            ],
            [
                'pay_period_start' => $payPeriodStart,
                'pay_period_end' => $payPeriodEnd,
                'gross_pay' => round($grossPay, 2),
                'deductions' => round($deductions, 2),
                'net_pay' => round($netPay, 2),
                'overtime_pay' => 0, // Placeholder
                'late_deductions' => 0, // Placeholder
                'absences_deductions' => 0, // Placeholder
                'total_hours_worked' => round($totalActualWorkHours, 2), // Use actual accumulated work hours
                'overtime_hours' => 0, // Placeholder
                'late_minutes' => 0, // Placeholder
                'absent_days' => ($actualWorkingDaysInPeriod - $presentDays), // Calculate absent days
                'details' => json_encode([
                    'monthly_salary' => $effectiveMonthlySalary,
                    'daily_rate' => $dailyRate,
                    'hourly_rate' => $hourlyRate,
                    'expected_working_days_in_period' => $actualWorkingDaysInPeriod,
                    'present_days' => $presentDays,
                    'holiday_working_days' => $totalHolidayWorkingDays,
                    'pay_period_type' => $employee->pay_schedule,
                    'sss_deduction' => round($sssDeduction, 2),
                    'sss_is_percentage' => $this->getContributionDetail('sss', $effectiveMonthlySalary, $governmentContributions, 'is_percentage', $employee),
                    'sss_employee_share_rate' => $this->getContributionDetail('sss', $effectiveMonthlySalary, $governmentContributions, 'employee_share', $employee),
                    'philhealth_deduction' => round($philhealthDeduction, 2),
                    'philhealth_is_percentage' => $this->getContributionDetail('philhealth', $effectiveMonthlySalary, $governmentContributions, 'is_percentage', $employee),
                    'philhealth_employee_share_rate' => $this->getContributionDetail('philhealth', $effectiveMonthlySalary, $governmentContributions, 'employee_share', $employee),
                    'pagibig_deduction' => round($pagibigDeduction, 2),
                    'pagibig_is_percentage' => $this->getContributionDetail('pagibig', $effectiveMonthlySalary, $governmentContributions, 'is_percentage', $employee),
                    'pagibig_employee_share_rate' => $this->getContributionDetail('pagibig', $effectiveMonthlySalary, $governmentContributions, 'employee_share', $employee),
                    'hourly_rate_computed' => $hourlyRate,
                    'pay_period_days_count' => $this->getDaysInPayPeriod($payPeriodStart, $payPeriodEnd),
                ]),
            ]
        );
    }

    /**
     * Calculates the number of days in a given pay period.
     *
     * @param Carbon $payPeriodStart
     * @param Carbon $payPeriodEnd
     * @return int
     */
    private function getDaysInPayPeriod(Carbon $payPeriodStart, Carbon $payPeriodEnd): int
    {
        return $payPeriodStart->diffInDays($payPeriodEnd) + 1;
    }

    /**
     * Calculates the government contribution for a given type and salary.
     *
     * @param string $type
     * @param float $salary
     * @param \Illuminate\Support\Collection $governmentContributions
     * @return float
     */
    private function calculateContribution(string $type, float $salary, \Illuminate\Support\Collection $governmentContributions, User $employee): float
    {
        $contributionsOfType = $governmentContributions->get($type);
        if (!$contributionsOfType) {
            return 0.00;
        }

        foreach ($contributionsOfType as $contribution) {
            $min = $contribution->min_salary;
            $max = $contribution->max_salary;

            if (($min === null || $salary >= $min) && ($max === null || $salary <= $max)) {
                // Check if the current employee is eligible for this fixed deduction
                if (!$contribution->is_percentage && !$this->isEmployeeEligibleForFixedContribution($employee, $contribution)) {
                    return 0.00; // Employee not eligible for this fixed deduction
                }

                if ($contribution->is_percentage) {
                    // Calculate as percentage of salary, capped at max_salary if defined
                    $applicableSalary = ($max !== null && $salary > $max) ? $max : $salary;
                    return ($applicableSalary * $contribution->employee_share) / 100;
                } else {
                    // Fixed amount deduction
                    return $contribution->employee_share;
                }
            }
        }
        return 0.00;
    }

    /**
     * Retrieves a specific detail from the government contribution for a given type and salary.
     *
     * @param string $type
     * @param float $salary
     * @param \Illuminate\Support\Collection $governmentContributions
     * @param string $detailKey
     * @return mixed
     */
    private function getContributionDetail(string $type, float $salary, \Illuminate\Support\Collection $governmentContributions, string $detailKey, User $employee): mixed
    {
        $contributionsOfType = $governmentContributions->get($type);
        if (!$contributionsOfType) {
            return null;
        }

        foreach ($contributionsOfType as $contribution) {
            $min = $contribution->min_salary;
            $max = $contribution->max_salary;

            if (($min === null || $salary >= $min) && ($max === null || $salary <= $max)) {
                // Check if the current employee is eligible for this fixed deduction
                if (!$contribution->is_percentage && !$this->isEmployeeEligibleForFixedContribution($employee, $contribution)) {
                    return null; // Employee not eligible for this fixed deduction
                }

                return $contribution->$detailKey;
            }
        }
        return null;
    }

    /**
     * Checks if an employee is eligible for a fixed contribution based on target_type and applies_to.
     *
     * @param User $employee
     * @param GovernmentContribution $contribution
     * @return bool
     */
    private function isEmployeeEligibleForFixedContribution(User $employee, GovernmentContribution $contribution): bool
    {
        if ($contribution->target_type === 'all') {
            return true;
        }

        if ($contribution->target_type === 'employees') {
            return in_array($employee->id, $contribution->applies_to ?? []);
        }

        if ($contribution->target_type === 'departments') {
            return in_array($employee->department_id, $contribution->applies_to ?? []);
        }

        return false;
    }

    /**
     * Calculates the number of actual working days in the given pay period,
     * considering selected working days and rest days.
     *
     * @param array $selectedWorkingDaysArr
     * @param array $selectedRestDaysArr
     * @param Carbon $payPeriodStart
     * @param Carbon $payPeriodEnd
     * @return int
     */
    private function getActualWorkingDaysInMonth(array $selectedWorkingDaysArr, array $selectedRestDaysArr, Carbon $payPeriodStart, Carbon $payPeriodEnd): int
    {
        $actualWorkingDays = 0;
        $currentDate = $payPeriodStart->copy();

        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        while ($currentDate->lte($payPeriodEnd)) {
            $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            $currentDayName = $dayNames[$dayOfWeek];

            if (in_array($currentDayName, $selectedWorkingDaysArr) && !in_array($currentDayName, $selectedRestDaysArr)) {
                $actualWorkingDays++;
            }
            $currentDate->addDay();
        }

        return $actualWorkingDays;
    }

    /**
     * Ensure day lists are always arrays with sensible defaults.
     *
     * @param mixed $days
     * @param array $default
     * @return array
     */
    private function normalizeDaysArray($days, array $default = []): array
    {
        if (is_array($days) && !empty($days)) {
            return array_values(array_filter($days));
        }

        if (is_string($days) && trim($days) !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $days))));
        }

        return $default;
    }
}
