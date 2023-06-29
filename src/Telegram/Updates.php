<?php
/**
 * Telegram Update class.
 * This class represents one or more updates from the Telegram API.
 *
 * @author Sebastiano Racca
 * @package TelegramSDK\BotAPI\Telegram
 */

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

#[\AllowDynamicProperties]
class Updates{

    /**
     * @var int|null The ID of the last update.
     */
    public ?int $lastUpdateID;

    /**
     * Updates constructor.
     *
     * @param object|null $data The data object containing Telegram updates.
     */
    public function __construct(?object $data){
        if($data !== NULL){

            if(isset($data->result[0]))
                $this->lastUpdateID = $data->result[array_key_last($data->result)]->update_id ?? null;
            else
                $this->lastUpdateID = null;


            foreach($data as $key => $value)
                $this->$key = $value;
        }
    }
}
