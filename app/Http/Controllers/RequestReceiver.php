<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use unreal4u\TelegramAPI\Telegram\Methods\AnswerInlineQuery;
use unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use unreal4u\TelegramAPI\Telegram\Methods\SendSticker;
use unreal4u\TelegramAPI\Telegram\Types\Chat;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Query\Result\Article;
use unreal4u\TelegramAPI\Telegram\Types\InputMessageContent\Text;
use unreal4u\TelegramAPI\Telegram\Types\Message;
use unreal4u\TelegramAPI\Telegram\Types\Sticker;
use unreal4u\TelegramAPI\Telegram\Types\Update;
use unreal4u\TelegramAPI\TgLog;
use App\Helpers\CutiMessageFormatter;
use Log;

/**
 * Class RequestReceiver
 * @package App\Http\Controllers
 *
 * Main receiver for telegram request
 */
class RequestReceiver extends Controller
{
    private $formatter;

    public function __construct(CutiMessageFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Main function which receives brunt of all requests
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {

        $updates = new Update($request->input());

        if (! empty($updates->inline_query)) {
            $this->testInline($updates);
            return response()->json([]);
        }

//        Log::debug(json_encode($updates));
//        Log::debug($updates->message->text);

        if (empty($updates->message->text) || $updates->message->from->is_bot) {
            return response()->json([]);
        }

        $messageText = $updates->message->text;
        $chatID = $updates->message->chat->id;

        $individualUserChat = false;
        if ($chatID >= 0) { // if update origin is from group, add bot name prefix requirement
            $individualUserChat = true;
        }

        if ($messageText == "/start@kapancuti_bot" || ($messageText == "/start" && $individualUserChat)) {

            $command = new SendMessage();
            $command->chat_id = $chatID;
            $command->text = "Untuk menjaga keseimbangan kerja dan liburan, ".
                "bot ini dibuat sebagai referensi untuk pengambilan cuti anda. ".
                "Ada 3 command, yaitu /all, /incoming, dan /recommendation. Silakan dicoba.\n\n ".
                "Kritik dan saran silakan hubungi @swallowstalker ya.";
            $command->parse_mode = "html";
            $this->executeApiRequest([$command]);
            $this->reportToAdmin($updates->message);

        } else if ($messageText == "/all@kapancuti_bot" || ($messageText == "/all" && $individualUserChat)) {

            $holidayList = Holiday::thisYear()->get();
            $prefixMessage = "Berikut adalah semua hari libur pada tahun ". date("Y");
            $holidayText = $this->formatter->prepareHolidayListMessage($holidayList, $prefixMessage);

            $command = new SendMessage();
            $command->chat_id = $chatID;
            $command->text = $holidayText;
            $command->parse_mode = "html";
            $this->executeApiRequest([$command]);

            $this->reportToAdmin($updates->message);

        } else if ($messageText == "/incoming@kapancuti_bot" || ($messageText == "/incoming" && $individualUserChat)) {

            $holidayList = Holiday::incoming()->get();
            $prefixMessage = "Berikut adalah hari libur untuk 3 bulan mendatang";
            $holidayText = $this->formatter->prepareHolidayListMessage($holidayList, $prefixMessage);

            $command = new SendMessage();
            $command->chat_id = $chatID;
            $command->text = $holidayText;
            $command->parse_mode = "html";
            $this->executeApiRequest([$command]);

            $this->reportToAdmin($updates->message);

        } else if ($messageText == "/recommendation@kapancuti_bot" || ($messageText == "/recommendation" && $individualUserChat)) {

            $holidayList = Holiday::incoming()->get();
            $prefixMessage = "Berikut adalah hari libur mendatang dan rekomendasi cuti untuk 3 bulan mendatang";
            $holidayText = $this->formatter->prepareHolidayListMessage($holidayList, $prefixMessage, true);

            $command = new SendMessage();
            $command->chat_id = $chatID;
            $command->text = $holidayText;
            $command->parse_mode = "html";
            $this->executeApiRequest([$command]);

            $this->reportToAdmin($updates->message);
        }

        return response()->json([]);
    }

    private function testInline(Update $update) {

        $inlineQueryResultArticle = new Article();
        $inlineQueryResultArticle->title = 'Incoming holidays';

        $inputMessageContentText = new Sticker();
        $inputMessageContentText->message_text = '/incoming@kapancuti_bot';

        $inlineQueryResultArticle->input_message_content = $inputMessageContentText;
        $inlineQueryResultArticle->id = md5('something unique that you can query on later');

        $answerInlineQuery = new AnswerInlineQuery();
        $answerInlineQuery->inline_query_id = $update->inline_query->id;
        $answerInlineQuery->addResult($inlineQueryResultArticle);

        $this->executeApiRequest([$answerInlineQuery]);
    }

    /**
     * For debugging purpose, to reproduce bug with untriggered request.
     */
    private function reportToAdmin(Message $message) {

        $adminChatID = env("MAINTAINER_CHAT_ID");
        $command = new SendMessage();
        $command->chat_id = $adminChatID;

        if ($message->chat->id < 0) {
            $command->text = "Report call group: ". $message->chat->title .", from: ". $message->from->first_name . " " .
                $message->from->last_name . "(". $message->from->username .")";
        } else {
            $command->text = "Report call private: ". $message->from->first_name . " " .
                $message->from->last_name . "(". $message->from->username .")";
        }

        $command->parse_mode = "html";
        $this->executeApiRequest([$command]);
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
    private function prepareHolidayListMessage($chatID,
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
                $holidayText .= "-------------------\n";
                $holidayText .= $this->formatter->prepareMonthText($holiday) . "\n";
            }

            if ($withRecommendation) {
                $holidayText .= "-------------------\n";
            }

            $holidayText .= $this->formatter->prepareRemainingDaysTextToHoliday($holiday) ."\n";
            $holidayText .= $this->formatter->prepareRangedHolidayText($holiday);

            $holidayText .= " (<b>". $holiday->description ."</b>)";

            if ($withRecommendation) {
                $holidayText .= "\n";
                $holidayText .= $this->formatter->prepareLeaveRecommendation($holiday);
            }

            if ($holiday->start->day === 0 || $holiday->end->day === 6) {
                $holidayText = "<i>". $holidayText ."</i>";
            }

            $responseText .= $holidayText;
        }

        $responseText .= "\nSelamat liburan!";
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
}
