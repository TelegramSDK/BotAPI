<?php
define("PRODUCTION", false);
require_once "vendor/autoload.php";

define("GREEN_TEXT", "\033[0;32m");
define("RED_TEXT", "\033[0;31m");
define("DEFAULT_TEXT", "\033[0m");

use TelegramSDK\BotAPI\Telegram\Bot;
$bot = new Bot("YOUR_BOT_TOKEN", Bot::UPDATES_FROM_GET_UPDATES);

echo GREEN_TEXT . "Bot Started!\n" . DEFAULT_TEXT;

while(true){
    $updates = $bot->updates($updates->lastUpdateID ?? null);

    foreach($updates->result as $update){
        if(isset($update->message)){
            $res = $bot->copyMessage([
                "chat_id" => $update->message->chat->id,
                "from_chat_id" => $update->message->chat->id,
                "message_id" => $update->message->message_id
            ]);

            if($res->body->ok){
                echo GREEN_TEXT . "Replied to " . $update->message->chat->id . "\n" . DEFAULT_TEXT;
            } else{
                echo RED_TEXT . "Coulnd't reply to " . $update->message->chat->id . ": " . $res->body->error . "\n" . DEFAULT_TEXT;
            }
        }
    }

    sleep(5);
}