<?php

require_once  "vendor/autoload.php";

use TelegramSDK\BotAPI\Exception\TelegramException;
use TelegramSDK\BotAPI\Telegram\{Bot, Update};


define("GREEN_COLOR", "\033[0;32m");
define("RED_COLOR", "\033[0;31m");
define("DEFAULT_COLOR", "\033[0m");


$bot = new Bot("YOUR_BOT_TOKEN", Update::UPDATES_FROM_GET_UPDATES);

if(!$bot->isValidToken(true)) {
    echo RED_COLOR . "Invalid bot token.\n" . DEFAULT_COLOR;
    exit(1);
}

echo GREEN_COLOR . "Bot Started!\n" . DEFAULT_COLOR;

for ( ; ; sleep(5)) {

    $updates = $bot->updates(true, isset($updates) ? $updates->getLastUpdateId() : null);

    foreach($updates->result as $update){
        if(isset($update->message)){

            try {

                $res = $bot->copyMessage([
                    "chat_id" => $update->chat->id,
                    "from_chat_id" => $update->chat->id,
                    "message_id" => $update->message->message_id
                ]);

                echo GREEN_COLOR . "Replied to " . $update->chat->id . "\n" . DEFAULT_COLOR;

            } catch (TelegramException $e) {
                echo RED_COLOR . "Coulnd't reply to " . $update->chat->id . ": " . $e->getResponseBody()->description . "\n" . DEFAULT_COLOR;
            }

        }
    }
}
