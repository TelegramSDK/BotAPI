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
    private ?object $data;
    private array $customs = [ ];
    public bool $ok;
    public object|array $result;

    public const UPDATES_FROM_GET_UPDATES = 1;
    public const UPDATES_FROM_WEBHOOK = 2;

    /**
     * Updates constructor.
     *
     * @param object|null $data The data object containing Telegram updates.
     * @param int $updatesMethod The updates method to use.
     */
    public function __construct(?object $data, int $updatesMethod)
    {
        self::validateUpdateMethod($updatesMethod);
        $this->method = $updatesMethod;

        $this->data = $data;
        $this->lastUpdateID = isset($data->result[0]) ? $data->result[array_key_last($data->result)]->update_id ?? null : null;

        foreach ($data ?? [] as $key => $value) {
            $this->$key = $value;
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

    /**
     * Get the message from the update.
     *
     * @return object|null The message object if present; otherwise, null.
     */
    public function getMessage(): ?object
    {
        if(!isset($this->customs['message'])) {
            $this->customs['message'] = $this->data->message ??
                $this->data->edited_message ??
                $this->data->channel_post ??
                $this->data->edited_channel_post ??
                $this->data->callback_query->message ??
                null;
        }

        return $this->customs['message'];
    }

    /**
     * Get the chat from the update.
     *
     * @return object|null The chat object if present; otherwise, null.
     */
    public function getChat(): ?object
    {
        if(!isset($this->customs['chat'])) {
            $this->customs['chat'] = $this->getMessage()->chat ??
                $this->data->callback_query->message->chat ??
                $this->data->my_chat_member->chat ??
                $this->data->chat_member->chat ??
                $this->data->chat_join_request->chat ??
                null;
        }

        return $this->customs['chat'];
    }

    /**
     * Get the user from the update.
     *
     * @return object|null The user object if present; otherwise, null.
     */
    public function getUser(): ?object
    {
        if(!isset($this->customs['user'])) {
            $this->customs['user'] = $this->data->callback_query->from ??
                $this->getMessage()->from ??
                $this->getMessage()->sender_chat ??
                $this->data->inline_query->from ??
                $this->data->chosen_inline_result->from ??
                $this->data->callback_query->from ??
                $this->data->shipping_query->from ??
                $this->data->poll_answer->user ??
                $this->data->chat_member->from ??
                $this->data->chat_join_request->from ??
                null;
        }

        return $this->customs['user'];
    }

    /**
     * Returns the trigger of the update.
     * A textual message in the chat.
     * i.e. A command, a caption, a callback data, ...
     *
     * @return string|null A string rappresenting the trigger.
     */
    public function getTrigger(): ?string
    {
        if(!isset($this->customs['trigger'])) {
            $message = $this->data->message ??
                $this->data->edited_message ??
                $this->data->channel_post ??
                $this->data->edited_channel_post ??
                null;

            $this->customs['trigger'] = $message->text ??
                $message->caption ??
                $this->data->inline_query->query ??
                $this->data->callback_query->data ??
                null;
        }

        return $this->customs['trigger'];
    }
}
