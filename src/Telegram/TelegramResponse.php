<?php

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

#[\AllowDynamicProperties]
class TelegramResponse{
    public function __construct(array|object $data){
        foreach($data as $key => $value)
            $this->$key = $value;
    }
}