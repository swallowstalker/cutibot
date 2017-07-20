<?php
/**
 * Created by PhpStorm.
 * User: pulungragil
 * Date: 1/16/17
 * Time: 6:03 PM
 */

namespace App\Helpers;

use App\Models\Holiday;
use App\Models\LeaveRecommendation;
use Carbon\Carbon;
use Storage;


/**
 * Class RawHolidayDataImporter
 *
 * Import raw json holiday data into MySQL related data.
 *
 * @package App\Helpers
 */
class RawHolidayDataImporter
{

    public function parseHolidayData() {

        $holidayRawData = Storage::get("2017.json");
        $holidayRawList = json_decode($holidayRawData, true);

        foreach ($holidayRawList as $holidayRaw) {

            $dateList = $holidayRaw["date_list"];

            if (! empty($dateList)) {

                $ignored = false;
                if ($holidayRaw["holiday_streak"]["start"] == null) {
                    $ignored = true;
                }

                $holiday = Holiday::firstOrCreate([
                    "description" => $holidayRaw["description"],
                    "start" => $dateList[0],
                    "end" => $dateList[count($dateList) - 1],
                    "recommendation_start" => $holidayRaw["holiday_streak"]["start"],
                    "recommendation_end" => $holidayRaw["holiday_streak"]["end"],
                    "ignored" => $ignored
                ]);

                foreach ($holidayRaw["leave_recommendation"] as $leaveRecommendation) {

                    LeaveRecommendation::firstOrCreate([
                        "holiday_id" => $holiday->id,
                        "leave_date" => $leaveRecommendation
                    ]);
                }

            }
        }
    }
}