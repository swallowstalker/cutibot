<?php

use App\Helpers\CutiMessageFormatter;
use Carbon\Carbon;
use App\Models\LeaveRecommendation;
use App\Models\Holiday;

class LeaveRecommendationTest extends TestCase
{
    private $formatter;

    public function setUp()
    {
        parent::setUp();
        $this->formatter = new CutiMessageFormatter();
    }

    public function testRecommendationExist()
    {
        $holiday = new Holiday();
        $holiday->start = "2017-12-15";
        $holiday->recommendation_start = "2017-12-14";
        $holiday->recommendation_end = "2017-12-18";

        $holiday->recommendations = collect([
            new LeaveRecommendation(["leave_date" => "2017-12-14"]),
            new LeaveRecommendation(["leave_date" => "2017-12-18"]),
        ]);

        $this->assertEquals(
            "<b>Rekomendasi cuti </b>(2 hari cuti, 5 hari libur)\n".
            "&#9737; Thursday 14 Dec\n"."&#9737; Monday 18 Dec\n".
            "Liburan dari Thursday 14 Dec - Monday 18 Dec",
            $this->formatter->prepareLeaveRecommendation($holiday)
        );
    }

    public function testRecommendationEmpty()
    {
        $holiday = new Holiday();
        $holiday->start = "2017-12-15";
        $holiday->recommendation_start = null;
        $holiday->recommendation_end = null;
        $holiday->ignored = true;

        $holiday->recommendations = collect([]);

        $this->assertEquals(
            "",
            $this->formatter->prepareLeaveRecommendation($holiday)
        );
    }


}
