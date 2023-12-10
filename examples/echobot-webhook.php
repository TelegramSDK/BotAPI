<?php

require_once  "vendor/autoload.php";

use TelegramSDK\BotAPI\Telegram\{Bot, Update};

$bot = new Bot("YOUR_BOT_TOKEN", Update::UPDATES_FROM_WEBHOOK);

$update = $bot->updates();

if(isset($update->update_id)) {

    if(isset($update->message)) {
        $chat = $update->getChat();

        $bot->copyMessage([
            "chat_id" => $chat->id,
            "from_chat_id" => $chat->id,
            "message_id" => $update->getMessage()->message_id
        ]);
    }

} else {
    echo "No updates from telegram where found.\n";
}
