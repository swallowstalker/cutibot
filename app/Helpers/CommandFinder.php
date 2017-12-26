<?php
/**
 * Created by PhpStorm.
 * User: pulung
 * Date: 26/12/17
 * Time: 19.05
 */

namespace App\Helpers;


class CommandFinder
{
    /**
     * Find bot command in the beginning of message. Only bot command.
     * Include bot username if chat is group chat.
     *
     * @param $rawMessage string telegram message text
     * @param $individualUserChat bool whether message is from group chat or individual chat
     * @return mixed|string
     */
    public function findCommand($rawMessage, $individualUserChat) {

        $matches = [];

        $bot_name_occurrence = "";
        if (! $individualUserChat) {
            $bot_name_occurrence = "(@". env("BOT_USERNAME") ."){1}";
        }

        preg_match("/^\/(start|year|all|incoming|recommendation)". $bot_name_occurrence ."/",
            $rawMessage, $matches);

        if (count($matches) > 1) {
            return $matches[1];
        }
        return "";
    }

    /**
     * Find year param after bot command.
     *
     * @param $rawMessage
     * @return mixed|string
     */
    public function findYearParams($rawMessage) {
        $matches = [];

        preg_match("/^\/[A-z@_]+ (\d+)/",
            $rawMessage, $matches);

        if (count($matches) > 1) {
            return $matches[1];
        }
        return "";
    }
}