<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimeHelper
{
    public static function getTimeOfDay(Carbon $time)
    {
        // Define the midday boundary (12:00 PM)
        $midday = Carbon::createFromTime(12, 0, 0, $time->timezone);

        if ($time->lessThan($midday)) {
            return 'Morning';
        } else {
            return 'Afternoon';
        }
    }
}
