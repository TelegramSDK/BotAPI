<?php

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

#[\AllowDynamicProperties]
class Updates{
    public ?int $lastUpdateID;

    public function __construct(?object $data){
        if($data !== NULL){
            $this->lastUpdateID = end($data->result)->update_id ?? null;
            foreach($data as $key => $value)
                $this->$key = $value;
        }
    }
}
