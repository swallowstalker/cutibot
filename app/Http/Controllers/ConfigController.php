<?php

namespace App\Http\Controllers;

use App\Helpers\RawHolidayDataImporter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use unreal4u\TelegramAPI\Telegram\Methods\GetMe;
use unreal4u\TelegramAPI\Telegram\Methods\GetUpdates;
use unreal4u\TelegramAPI\Telegram\Methods\GetUserProfilePhotos;
use unreal4u\TelegramAPI\Telegram\Methods\GetWebhookInfo;
use unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use unreal4u\TelegramAPI\Telegram\Methods\SetWebhook;
use unreal4u\TelegramAPI\TgLog;

class ConfigController extends Controller
{

    public function setWebhook() {

        $tgLog = new TgLog(env("TELEGRAM_BOT_TOKEN"));

        $setWebhook = new SetWebhook();
        $setWebhook->url = url("/update");
        $setWebhook->certificate = File::get(storage_path(env("PEM_TELEGRAM_PATH")));

        $response = $tgLog->performApiRequest($setWebhook);

        return response()->json($response);
    }

    public function getWebhookInfo() {

        $tgLog = new TgLog(env("TELEGRAM_BOT_TOKEN"));

        $getWebhookInfo = new GetWebhookInfo();

        $response = $tgLog->performApiRequest($getWebhookInfo);

        return response()->json($response);
    }

    public function importHolidayData() {

        $importer = new RawHolidayDataImporter();
        $importer->parseHolidayData();

        return response()->json([]);
    }

    public function test() {

    }
}
