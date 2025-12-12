<?php

namespace App\Services;

use Carbon\Carbon;

class PhilippineHolidayService
{
    /**
     * Get all Philippine holidays for a given year
     * 
     * @param int $year
     * @return array Array of holiday data with date, name, type, and rate_multiplier
     */
    public function getHolidaysForYear(int $year): array
    {
        $holidays = [];

        // Regular Holidays (rate_multiplier: 2.00)
        $holidays = array_merge($holidays, $this->getRegularHolidays($year));
        
        // Special Non-Working Holidays (rate_multiplier: 1.30)
        $holidays = array_merge($holidays, $this->getSpecialNonWorkingHolidays($year));

        // Sort by date
        usort($holidays, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $holidays;
    }

    /**
     * Get regular holidays (rate_multiplier: 2.00)
     * 
     * @param int $year
     * @return array
     */
    private function getRegularHolidays(int $year): array
    {
        $holidays = [];
        $easterDate = $this->calculateEaster($year);

        // Fixed dates
        $holidays[] = [
            'date' => Carbon::create($year, 1, 1)->format('Y-m-d'),
            'name' => "New Year's Day",
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        // Maundy Thursday (Thursday before Easter)
        $holidays[] = [
            'date' => $easterDate->copy()->subDays(3)->format('Y-m-d'),
            'name' => 'Maundy Thursday',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        // Good Friday (Friday before Easter)
        $holidays[] = [
            'date' => $easterDate->copy()->subDays(2)->format('Y-m-d'),
            'name' => 'Good Friday',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 4, 9)->format('Y-m-d'),
            'name' => 'Araw ng Kagitingan',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 5, 1)->format('Y-m-d'),
            'name' => 'Labor Day',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 6, 12)->format('Y-m-d'),
            'name' => 'Independence Day',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        // National Heroes Day (last Monday of August)
        $lastMonday = Carbon::create($year, 8, 31)->startOfMonth()->endOfMonth();
        while ($lastMonday->dayOfWeek !== Carbon::MONDAY) {
            $lastMonday->subDay();
        }
        $holidays[] = [
            'date' => $lastMonday->format('Y-m-d'),
            'name' => 'National Heroes Day',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 11, 30)->format('Y-m-d'),
            'name' => 'Bonifacio Day',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 12, 30)->format('Y-m-d'),
            'name' => 'Rizal Day',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 12, 25)->format('Y-m-d'),
            'name' => 'Christmas Day',
            'type' => 'regular',
            'rate_multiplier' => 2.00,
        ];

        return $holidays;
    }

    /**
     * Get special non-working holidays (rate_multiplier: 1.30)
     * 
     * @param int $year
     * @return array
     */
    private function getSpecialNonWorkingHolidays(int $year): array
    {
        $holidays = [];
        $easterDate = $this->calculateEaster($year);

        // Chinese New Year (calculated based on lunar calendar)
        // Using a lookup table for common years (can be extended)
        $chineseNewYearDates = $this->getChineseNewYearDates();
        
        if (isset($chineseNewYearDates[$year])) {
            $holidays[] = [
                'date' => $chineseNewYearDates[$year],
                'name' => 'Chinese New Year',
                'type' => 'special_non_working',
                'rate_multiplier' => 1.30,
            ];
        }

        $holidays[] = [
            'date' => Carbon::create($year, 2, 25)->format('Y-m-d'),
            'name' => 'EDSA People Power Revolution',
            'type' => 'special_non_working',
            'rate_multiplier' => 1.30,
        ];

        // Black Saturday (Saturday before Easter)
        $holidays[] = [
            'date' => $easterDate->copy()->subDays(1)->format('Y-m-d'),
            'name' => 'Black Saturday',
            'type' => 'special_non_working',
            'rate_multiplier' => 1.30,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 8, 21)->format('Y-m-d'),
            'name' => 'Ninoy Aquino Day',
            'type' => 'special_non_working',
            'rate_multiplier' => 1.30,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 11, 1)->format('Y-m-d'),
            'name' => "All Saints' Day",
            'type' => 'special_non_working',
            'rate_multiplier' => 1.30,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 11, 2)->format('Y-m-d'),
            'name' => "All Souls' Day",
            'type' => 'special_non_working',
            'rate_multiplier' => 1.30,
        ];

        $holidays[] = [
            'date' => Carbon::create($year, 12, 31)->format('Y-m-d'),
            'name' => 'Last Day of the Year',
            'type' => 'special_non_working',
            'rate_multiplier' => 1.30,
        ];

        return $holidays;
    }

    /**
     * Calculate Easter date for a given year using the Computus algorithm
     * 
     * @param int $year
     * @return Carbon
     */
    private function calculateEaster(int $year): Carbon
    {
        // Computus algorithm to calculate Easter
        $a = $year % 19;
        $b = intval($year / 100);
        $c = $year % 100;
        $d = intval($b / 4);
        $e = $b % 4;
        $f = intval(($b + 8) / 25);
        $g = intval(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intval($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intval(($a + 11 * $h + 22 * $l) / 451);
        $month = intval(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day);
    }

    /**
     * Get Chinese New Year dates (lunar calendar based)
     * These dates are fixed for each year and typically fall between Jan 21 - Feb 20
     * 
     * @return array
     */
    private function getChineseNewYearDates(): array
    {
        // Chinese New Year dates from 2020-2030
        // Source: Based on actual lunar calendar calculations
        return [
            2020 => '2020-01-25',
            2021 => '2021-02-12',
            2022 => '2022-02-01',
            2023 => '2023-01-22',
            2024 => '2024-02-10',
            2025 => '2025-01-29',
            2026 => '2026-02-17',
            2027 => '2027-02-06',
            2028 => '2028-01-26',
            2029 => '2029-02-13',
            2030 => '2030-02-03',
            2031 => '2031-01-23',
            2032 => '2032-02-11',
            2033 => '2033-01-31',
            2034 => '2034-02-19',
            2035 => '2035-02-08',
            2036 => '2036-01-28',
            2037 => '2037-02-15',
            2038 => '2038-02-04',
            2039 => '2039-01-24',
            2040 => '2040-02-12',
        ];
    }
}
