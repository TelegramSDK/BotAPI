<?php
/**
 * Telegram Response class.
 * This class represents a response from the Telegram API.
 *
 * @author Sebastiano Racca
 * @package TelegramSDK\BotAPI\Telegram
 */

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

#[\AllowDynamicProperties]
class TelegramResponse{

    /**
     * TelegramResponse constructor.
     * @param array|object $data The data containing the Telegram response.
     */
    public function __construct(array|object $data){
        foreach($data as $key => $value)
            $this->$key = $value;
    }
}