<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimeHelper
{
    public static function getTimeOfDay(Carbon $time)
    {
        // Define the midday boundary (12:00 PM) on the same date as the time being checked
        $midday = $time->copy()->setTime(12, 0, 0);

        if ($time->lessThan($midday)) {
            return 'Morning';
        } else {
            return 'Afternoon';
        }
    }
}
