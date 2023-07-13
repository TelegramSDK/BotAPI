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
     * @param object|null $data            The data object containing Telegram updates.
     * @param bool $enableDefaultUpdates   Whether the default updates should be enabled or not.
     */
    public function __construct(?object $data, bool $enableDefaultUpdates = false){
        if($data !== NULL){

            if(isset($data->result[0]))
                $this->lastUpdateID = $data->result[array_key_last($data->result)]->update_id ?? null;
            else
                $this->lastUpdateID = null;

            if($enableDefaultUpdates){

                foreach($data->result ?? [$data] as &$upd){
                    $upd->user = $upd->message->from ??
                        $upd->message->sender_chat ??
                        $upd->edited_message->from ??
                        $upd->inline_query->from ??
                        $upd->chosen_inline_result->from ??
                        $upd->callback_query->from ??
                        $upd->shipping_query->from ??
                        $upd->poll_answer->user ??
                        $upd->chat_member->from ??
                        $upd->chat_join_request->from ??
                        null;

                    $upd->chat = $upd->message->chat ??
                        $upd->edited_message->chat ??
                        $upd->channel_post->chat ??
                        $upd->edited_channel_post->chat ??
                        $upd->callback_query->message->chat ??
                        $upd->my_chat_member->chat ??
                        $upd->chat_member->chat ??
                        $upd->chat_join_request->chat ??
                        null;
                }
            }

            foreach($data as $key => $value)
                $this->$key = $value;
        }
    }
}
