<?php

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

/**
 * Telegram Response class.
 * This class represents a response from the Telegram API.
 *
 * @author Sebastiano Racca
 * @package TelegramSDK\BotAPI\Telegram
 */
class TelegramResponse
{
    public object|array $body;
    public ?int $statusCode;
    public ?string $error;

    /**
     * TelegramResponse constructor.
     *
     * @param object|array $body The response body from the Telegram API.
     * @param int|null $statusCode The HTTP status code of the response.
     * @param string|null $error Any error message associated with the response.
     */
    public function __construct(object|array $body, ?int $statusCode = null, ?string $error = null)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->error = $error;
    }
}
