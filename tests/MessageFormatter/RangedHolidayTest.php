<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Helpers\CutiMessageFormatter;
use Carbon\Carbon;

class RangedHolidayTest extends TestCase
{
    private $formatter;

    public function setUp()
    {
        parent::setUp();
        $this->formatter = new CutiMessageFormatter();
    }

    public function testSingleDayHoliday()
    {
        Carbon::setLocale("id");
        $startHoliday = Carbon::createFromFormat("Y-m-d", "2017-01-01");
        $endHoliday = Carbon::createFromFormat("Y-m-d", "2017-01-01");

        $holiday = new stdClass();
        $holiday->start = $startHoliday;
        $holiday->end = $endHoliday;

        $this->assertEquals(
            "&#9899; Sunday  1 Jan", //FIXME still cannot localize english into indonesia
            $this->formatter->prepareRangedHolidayText($holiday)
        );
    }

    public function testRangedHoliday()
    {
        Carbon::setLocale("id");
        $startHoliday = Carbon::createFromFormat("Y-m-d", "2017-01-01");
        $endHoliday = Carbon::createFromFormat("Y-m-d", "2017-01-02");

        $holiday = new stdClass();
        $holiday->start = $startHoliday;
        $holiday->end = $endHoliday;

        $this->assertEquals(
            "&#9899; Sunday  1 Jan - Monday  2 Jan", //FIXME still cannot localize english into indonesia
            $this->formatter->prepareRangedHolidayText($holiday)
        );
    }
}
