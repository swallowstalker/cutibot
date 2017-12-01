<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Helpers\CutiMessageFormatter;
use Carbon\Carbon;

class MessageFormatTest extends TestCase
{
    private $formatter;

    public function setUp()
    {
        parent::setUp();
        $this->formatter = new CutiMessageFormatter();
    }

    public function testFutureHoliday()
    {
        $expectedTime = Carbon::now();
        $expectedTime->addDay(1);

        $holiday = new stdClass();
        $holiday->start = $expectedTime;

        $this->assertEquals(
            "(1 hari lagi)",
            $this->formatter->prepareRemainingDaysTextToHoliday($holiday)
        );
    }
    public function testTodayIsHoliday()
    {
        $expectedTime = Carbon::now();

        $holiday = new stdClass();
        $holiday->start = $expectedTime;

        $this->assertEquals(
            "(Liburan sudah lewat)",
            $this->formatter->prepareRemainingDaysTextToHoliday($holiday)
        );
    }

    public function testPastHoliday()
    {
        $expectedTime = Carbon::now();
        $expectedTime->subDay(3);

        $holiday = new stdClass();
        $holiday->start = $expectedTime;

        $this->assertEquals(
            "(Liburan sudah lewat)",
            $this->formatter->prepareRemainingDaysTextToHoliday($holiday)
        );
    }
}
