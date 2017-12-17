<?php

use App\Helpers\CutiMessageFormatter;
use Carbon\Carbon;
use App\Models\LeaveRecommendation;
use App\Models\Holiday;

class HolidayListMessageTest extends TestCase
{
    private $formatter;
    private $holidayList;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->formatter = new CutiMessageFormatter();
    }

    public function setUp()
    {
        parent::setUp();

        $firstHoliday = new Holiday([
            "start" => "2017-12-15",
            "end" => "2017-12-15",
            "recommendation_start" => "2017-12-14",
            "recommendation_end" => "2017-12-18"
        ]);

        $firstHoliday->recommendations = collect([
            new LeaveRecommendation(["leave_date" => "2017-12-14"]),
            new LeaveRecommendation(["leave_date" => "2017-12-18"]),
        ]);

        $secondHoliday = new Holiday([
            "start" => "2017-12-08",
            "end" => "2017-12-08",
            "recommendation_start" => "2017-12-07",
            "recommendation_end" => "2017-12-11"
        ]);

        $secondHoliday->recommendations = collect([
            new LeaveRecommendation(["leave_date" => "2017-12-07"]),
            new LeaveRecommendation(["leave_date" => "2017-12-11"]),
        ]);

        $this->holidayList = collect([
            $firstHoliday,
            $secondHoliday
        ]);
    }

    public function testRecommendationExist()
    {
        // with prefix
        $result = $this->formatter->prepareHolidayListMessage(
            $this->holidayList,
            "Ini adalah jadwal kemungkinan cuti anda.",
            true
        );

        $expected =
            "Ini adalah jadwal kemungkinan cuti anda.\n".
            "-------------------\n".
            "<b>December 2017</b>\n".
            "-------------------\n".

            "(Liburan sudah lewat)\n".
            "&#9899; Friday 15 Dec (<b></b>)\n".
            "<b>Rekomendasi cuti </b>(2 hari cuti, 5 hari libur)\n".
            "&#9737; Thursday 14 Dec\n".
            "&#9737; Monday 18 Dec\n".
            "Liburan dari Thursday 14 Dec - Monday 18 Dec\n".

            "-------------------\n".
            "(Liburan sudah lewat)\n".
            "&#9899; Friday  8 Dec (<b></b>)\n".
            "<b>Rekomendasi cuti </b>(2 hari cuti, 5 hari libur)\n".
            "&#9737; Thursday  7 Dec\n".
            "&#9737; Monday 11 Dec\n".
            "Liburan dari Thursday  7 Dec - Monday 11 Dec\n".

            "Selamat liburan!";

        $this->assertEquals($expected, $result);


        // without prefix
        $result = $this->formatter->prepareHolidayListMessage(
            $this->holidayList,
            "",
            true
        );

        $expected =
            "\n".
            "-------------------\n".
            "<b>December 2017</b>\n".
            "-------------------\n".

            "(Liburan sudah lewat)\n".
            "&#9899; Friday 15 Dec (<b></b>)\n".
            "<b>Rekomendasi cuti </b>(2 hari cuti, 5 hari libur)\n".
            "&#9737; Thursday 14 Dec\n".
            "&#9737; Monday 18 Dec\n".
            "Liburan dari Thursday 14 Dec - Monday 18 Dec\n".

            "-------------------\n".
            "(Liburan sudah lewat)\n".
            "&#9899; Friday  8 Dec (<b></b>)\n".
            "<b>Rekomendasi cuti </b>(2 hari cuti, 5 hari libur)\n".
            "&#9737; Thursday  7 Dec\n".
            "&#9737; Monday 11 Dec\n".
            "Liburan dari Thursday  7 Dec - Monday 11 Dec\n".

            "Selamat liburan!";

        $this->assertEquals($expected, $result);
    }

    public function testNoRecommendation() {

        // with prefix
        $result = $this->formatter->prepareHolidayListMessage(
            $this->holidayList,
            "Ini adalah jadwal kemungkinan cuti anda."
        );

        $expected =
            "Ini adalah jadwal kemungkinan cuti anda.\n".
            "-------------------\n".
            "<b>December 2017</b>\n".
            "(Liburan sudah lewat)\n".
            "&#9899; Friday 15 Dec (<b></b>)\n".
            "(Liburan sudah lewat)\n".
            "&#9899; Friday  8 Dec (<b></b>)\n".
            "Selamat liburan!";

        $this->assertEquals($expected, $result);


        // without prefix
        $result = $this->formatter->prepareHolidayListMessage(
            $this->holidayList
        );

        $expected =
            "\n".
            "-------------------\n".
            "<b>December 2017</b>\n".
            "(Liburan sudah lewat)\n".
            "&#9899; Friday 15 Dec (<b></b>)\n".
            "(Liburan sudah lewat)\n".
            "&#9899; Friday  8 Dec (<b></b>)\n".
            "Selamat liburan!";

        $this->assertEquals($expected, $result);
    }
}
