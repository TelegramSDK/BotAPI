<?php
declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

use TelegramSDK\BotAPI\Exception\InvalidUpdateException;


/**
 * Telegram Update class.
 * This class represents one or more updates from the Telegram API.
 *
 * @author Sebastiano Racca
 * @package TelegramSDK\BotAPI\Telegram
 * @link https://botapi.racca.me/docs/usage/updates
 * @link https://botapi.racca.me/docs/usage/security
 */
#[\AllowDynamicProperties]
class Update
{
    private int $method;
    private ?int $lastUpdateID;
    public bool $ok;
    public object|array $result;

    public const UPDATES_FROM_GET_UPDATES = 1;
    public const UPDATES_FROM_WEBHOOK = 2;

    /**
     * Updates constructor.
     *
     * @param object|null $data The data object containing Telegram updates.
     * @param int $updatesMethod The updates method to use.
     * @param bool $enableDefaultUpdates Whether the default updates should be enabled or not.
     */
    public function __construct(?object $data, int $updatesMethod, bool $enableDefaultUpdates = false)
    {
        self::validateUpdateMethod($updatesMethod);
        $this->method = $updatesMethod;

        if ($data !== null) {
            $this->lastUpdateID = isset($data->result[0]) ? $data->result[array_key_last($data->result)]->update_id ?? null : null;

            if ($enableDefaultUpdates) {
                $data = $this->getDefaultUpdates($data);
            }

            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Checks if the provided updates method is valid.
     *
     * @param int $method The updates method.
     *
     * @throws InvalidUpdateException If the provided updates method is invalid.
     */
    public static function validateUpdateMethod(int $method): void
    {
        if (!in_array($method, [self::UPDATES_FROM_GET_UPDATES, self::UPDATES_FROM_WEBHOOK])) {
            throw new InvalidUpdateException("The provided updates method is invalid");
        }
    }

    private function getDefaultUpdates(object $data): object
    {
        foreach($data->result ?? [$data] as &$upd) {
            $upd->msg = $upd->message ??
                $upd->edited_message ??
                $upd->channel_post ??
                $upd->edited_channel_post ??
                $upd->callback_query->message ??
                null;

            $upd->user = $upd->callback_query->from ??
                $upd->msg->from ??
                $upd->msg->sender_chat ??
                $upd->inline_query->from ??
                $upd->chosen_inline_result->from ??
                $upd->callback_query->from ??
                $upd->shipping_query->from ??
                $upd->poll_answer->user ??
                $upd->chat_member->from ??
                $upd->chat_join_request->from ??
                null;

            $upd->chat = $upd->msg->chat ??
                $upd->callback_query->message->chat ??
                $upd->my_chat_member->chat ??
                $upd->chat_member->chat ??
                $upd->chat_join_request->chat ??
                null;
        }

        return $data;
    }

    /**
     * Checks if the update is from Telegram.
     *
     * @param string|null $secretToken The secret token for additional security.
     *
     * @return bool True if the update is from Telegram; otherwise, false.
     *
     * @throws InvalidUpdateException If updates are requested without using a webhook.
     */
    public function isFromTelegram(?string $secretToken = null): bool
    {
        if ($this->method === self::UPDATES_FROM_GET_UPDATES) {
            throw new InvalidUpdateException("You won't receive updates from Telegram if you don't use a webhook");
        }

        if (isset($secretToken)) {
            return $secretToken === ($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? null);
        }

        trigger_error("It is highly recommended to set up a secret token.", E_USER_WARNING);

        return strpos($_SERVER['HTTP_USER_AGENT'] ?? "", 'TelegramBot') === false;
    }

    /**
     * Get the last update ID.
     *
     * @return int|null The last update ID.
     */
    public function getLastUpdateId(): ?int
    {
        return $this->lastUpdateID;
    }
}
