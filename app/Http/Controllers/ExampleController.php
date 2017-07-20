<?php

namespace App\Http\Controllers;

use unreal4u\TelegramAPI\Telegram\Methods\GetMe;
use unreal4u\TelegramAPI\Telegram\Methods\GetUpdates;
use unreal4u\TelegramAPI\Telegram\Methods\GetUserProfilePhotos;
use unreal4u\TelegramAPI\Telegram\Methods\SendMessage;
use unreal4u\TelegramAPI\Telegram\Methods\SetWebhook;
use unreal4u\TelegramAPI\TgLog;

class ExampleController extends Controller
{

    public function index() {
        return response()->json("Hello world");
    }

    public function updates() {

        $tgLog = new TgLog(env("TELEGRAM_BOT_TOKEN"));

        $getUpdates = new GetUpdates();
        $response = $tgLog->performApiRequest($getUpdates);

        return response()->json($response);
    }
}
