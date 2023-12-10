<?php

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Exception;

/**
 * TelegramException class represents exceptions specific to the Telegram SDK.
 *
 * This exception is used to handle errors related to Telegram API requests.
 * It includes details such as the HTTP status code and the response body associated with the exception.
 *
 * @package TelegramSDK\BotAPI\Exception
 * @package TelegramSDK\BotAPI\Telegram
 */
class TelegramException extends \Exception
{
    /**
     * @var array|object|null The response body associated with the exception.
     */
    private array|object|null $responseBody;

    /**
     * @var int|null The HTTP status code associated with the exception.
     */
    private ?int $httpStatusCode;

    /**
     * TelegramException constructor.
     *
     * @param string $message The exception message.
     * @param int|null $httpStatusCode The HTTP status code associated with the exception.
     * @param array|object|null $responseBody The response body associated with the exception.
     */
    public function __construct(string $message = "", ?int $httpStatusCode = null, array|object|null $responseBody = null)
    {
        parent::__construct($message);
        $this->httpStatusCode = $httpStatusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * Get the response body associated with the exception.
     *
     * @return array|object|null The response body.
     */
    public function getResponseBody(): array|object|null
    {
        return $this->responseBody;
    }

    /**
     * Get the HTTP status code associated with the exception.
     *
     * @return int|null The HTTP status code.
     */
    public function getHttpCode(): ?int
    {
        return $this->httpStatusCode;
    }
}
