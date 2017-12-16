<?php

use App\Helpers\CutiMessageFormatter;
use Carbon\Carbon;

class PrepareMonthTest extends TestCase
{
    private $formatter;

    public function setUp()
    {
        parent::setUp();
        $this->formatter = new CutiMessageFormatter();
    }

    public function testMonthYear()
    {
        $expectedTime = Carbon::createFromFormat("Y-m-d", "2017-12-16");

        $holiday = new stdClass();
        $holiday->start = $expectedTime;

        $this->assertEquals(
            "<b>December 2017</b>",
            $this->formatter->prepareMonthText($holiday)
        );
    }

}
