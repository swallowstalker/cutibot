<?php

namespace App\Helpers;
/**
 * Created by PhpStorm.
 * User: pulung
 * Date: 01/12/17
 * Time: 20.44
 */

use Carbon\Carbon;


/**
 * Class CutiMessageFormatter
 * @package App\Helpers
 *
 * Format given holiday into respective message text
 * to send to users.
 */
class CutiMessageFormatter {

    public function __construct()
    {
        Carbon::setLocale("id");
    }

    /**
     * Get remaining days left to holiday
     *
     * @param $holiday
     * @return string
     */
    public function prepareRemainingDaysTextToHoliday($holiday): string
    {
        $currentDate = Carbon::now();
        $daysToHolidayInHuman = $currentDate->diffForHumans($holiday->start, true);

        $daysToHoliday = $currentDate->diffInDays($holiday->start, false) + 1;
        if ($daysToHoliday > 1) {
            $holidayText = "(". $daysToHolidayInHuman . " lagi)";
        } else {
            $holidayText = "(Liburan sudah lewat)";
        }

        return $holidayText;
    }

    /**
     * @param $holiday
     * @return string
     */
    public function prepareRangedHolidayText($holiday): string
    {
        $holidayText = "";
        if ($holiday->start == $holiday->end) {
            $holidayText = "&#9899; ". $holiday->start->formatLocalized("%A %e %b");
        } else {
            $holidayText = "&#9899; ". $holiday->start->formatLocalized("%A %e %b") .
                " - " . $holiday->end->formatLocalized("%A %e %b");
        }
        return $holidayText;
    }

    /**
     * @param $holiday
     * @return string
     */
    public function prepareMonthText($holiday): string
    {
        $holidayText = "<b>". $holiday->start->formatLocalized("%B %Y") . "</b>";
        return $holidayText;
    }
}

