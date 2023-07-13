<?php
define("PRODUCTION", false);
require_once "vendor/autoload.php";

use TelegramSDK\BotAPI\Telegram\Bot;

$bot = new Bot("YOUR_BOT_TOKEN", Bot::UPDATES_FROM_WEBHOOK);

$update = $bot->updates(true);

if(isset($update->update_id)){

    if(isset($update->message)){
        $bot->copyMessage([
            "chat_id" => $update->chat->id,
            "from_chat_id" => $update->chat->id,
            "message_id" => $update->message->message_id
        ]);
    }

} else{
    echo "No updates from telegram where found.\n";
}