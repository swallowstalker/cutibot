<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use unreal4u\TelegramAPI\Telegram\Methods\SendSticker;
use unreal4u\TelegramAPI\TgLog;

class RequestReceiver extends Controller
{
    /**
     * Main function which receives brunt of all requests
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {

        $response = $request->input();

        $message = $response["message"];
        $messageText = $message["text"];
        $chatID = $message["chat"]["id"];

        // TODO add help message if no command is entered

        if ($messageText == "/start" || $messageText == "/start@kapancuti_bot") {

            $command = new SendMessage();
            $command->chat_id = $chatID;
            $command->text = "Untuk menjaga keseimbangan kerja dan liburan, ".
                "bot ini dibuat sebagai referensi untuk pengambilan cuti anda. ".
                "Ada 3 command, yaitu /all, /incoming, dan /recommendation. Silakan dicoba.\n\n ".
                "Kritik dan saran silakan hubungi @swallowstalker ya.";
            $command->parse_mode = "html";
            $this->executeApiRequest([$command]);

        } else if ($messageText == "/all" || $messageText == "/all@kapancuti_bot") {

            $holidayList = Holiday::all();
            $prefixMessage = "Berikut adalah semua hari libur pada tahun ". date("Y") ."\n";
            $requests = $this->prepareholidayListMessage($chatID, $holidayList, $prefixMessage);
            $this->executeApiRequest($requests);

        } else if ($messageText == "/incoming" || $messageText == "/incoming@kapancuti_bot") {

            $holidayList = Holiday::incoming()->get();
            $prefixMessage = "Berikut adalah hari libur mendatang pada tahun ". date("Y") ."\n";
            $requests = $this->prepareholidayListMessage($chatID, $holidayList, $prefixMessage);
            $this->executeApiRequest($requests);

        } else if ($messageText == "/recommendation" || $messageText == "/recommendation@kapancuti_bot") {

            $holidayList = Holiday::incoming()->get();
            $prefixMessage = "Berikut adalah hari libur mendatang dan rekomendasi cuti pada tahun ".
                date("Y") ."\n";
            $requests = $this->prepareholidayListMessage($chatID, $holidayList, $prefixMessage, true);
            $this->executeApiRequest($requests);
        }

        return response()->json([]);
    }

    /**
     * Prepare list of holiday messages
     *
     * @param $chatID
     * @param $holidayList
     * @param string $prefixMessage
     * @param bool $withRecommendation
     * @return array
     */
    private function prepareholidayListMessage($chatID,
                                              $holidayList,
                                              string $prefixMessage = "",
                                              bool $withRecommendation = false) : array {

        $sendMessage = new SendMessage();
        $sendMessage->chat_id = $chatID;

        $responseText = $prefixMessage;
        $currentMonth = null;
        foreach ($holidayList as $holiday) {

            $holidayText = "\n";
            if ($currentMonth != $holiday->start->month) {
                list($currentMonth, $holidayText) = $this->prepareMonthText($holiday, $holidayText);
            }

            $holidayText .= "-------------------\n";

            $holidayText = $this->prepareRemainingDaysTextToHoliday($holiday, $holidayText);
            $holidayText = $this->prepareRangedHolidayText($holiday, $holidayText);

            $holidayText .= " (<b>". $holiday->description ."</b>)\n";

            if (! $holiday->ignored and $withRecommendation) {
                $holidayText = $this->prepareLeaveRecommendation($holiday, $holidayText);
            }

            if ($holiday->start->day === 0 || $holiday->end->day === 6) {
                $holidayText = "<i>". $holidayText ."</i>";
            }

            $responseText .= $holidayText;
        }

        $responseText .= "\nSelamat liburan!\n\n";
        $sendMessage->text = $responseText;
        $sendMessage->parse_mode = "html";

        return [$sendMessage];
    }

    /**
     * Temporary message for incomplete command.
     * @param $chatID
     * @return array
     */
    private function prepareUnbuiltCommandMessage($chatID) : array {

        $sendMessage = new SendMessage();
        $sendMessage->chat_id = $chatID;
        $sendMessage->text = "Tunggu ya, belum dibuat nih developernya lagi sibuk.";

        $sendSticker = new SendSticker();
        $sendSticker->sticker = "BQADBQADnQADwI3xAAGTw3GLlSM-zgI";
        $sendSticker->chat_id = $chatID;

        return [$sendMessage, $sendSticker];
    }

    /**
     * Execute API request to telegram server
     * @param array $requests
     */
    private function executeApiRequest(array $requests) {

        $tgLog = new TgLog(env("TELEGRAM_BOT_TOKEN"));

        foreach ($requests as $request) {
            $tgLog->performApiRequest($request);
        }
    }

    /**
     * Get remaining days left to holiday
     *
     * @param $holiday
     * @param $holidayText
     * @return string
     */
    private function prepareRemainingDaysTextToHoliday($holiday, $holidayText): string
    {
        $currentDate = Carbon::now();
        $daysToHoliday = $currentDate->diffInDays($holiday->start, false) + 1;
        if ($daysToHoliday > 0) {
            $holidayText .= "(". $daysToHoliday . " hari lagi)\n";
        } else {
            $holidayText .= "(Liburan sudah lewat)\n";
        }
        return $holidayText;
    }

    /**
     * @param $holiday
     * @param $holidayText
     * @return string
     */
    private function prepareRangedHolidayText($holiday, $holidayText): string
    {
        if ($holiday->start == $holiday->end) {
            $holidayText .= $holiday->start->format("l, j");
        } else {
            $holidayText .= $holiday->start->format("l, j") .
                " - " . $holiday->end->format("l, j");
        }
        return $holidayText;
    }

    /**
     * @param $holiday
     * @param $holidayText
     * @return array
     */
    private function prepareMonthText($holiday, $holidayText): array
    {
        $currentMonth = $holiday->start->month;
        $holidayText .= "\n" . $holiday->start->format("F Y") . "\n";
        return array($currentMonth, $holidayText);
    }

    /**
     * Get list of leave that employee should take in recommendations
     *
     * @param $holiday
     * @param $holidayText
     * @return string
     */
    private function prepareLeaveRecommendation($holiday, $holidayText): string
    {
        $differenceDay = $holiday->recommendation_start->diffInDays($holiday->recommendation_end) + 1;
        $leaveDateList = $holiday->recommendations->pluck("leave_date_formatted")->toArray();

        $holidayText .= "<b>Rekomendasi cuti </b>".
            "(". count($leaveDateList) . " hari cuti, ".
            $differenceDay . " hari libur) \n";

        foreach ($leaveDateList as $leaveDate) {
            $holidayText .= "&gt; " . $leaveDate . "\n";
        }

        $holidayText .= "Liburan dari " . $holiday->recommendation_start->format("l, j") .
            " - " . $holiday->recommendation_end->format("l, j") . "\n";
        return $holidayText;
    }
}
