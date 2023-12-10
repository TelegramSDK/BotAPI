<?php
declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

use TelegramSDK\BotAPI\Exception\TelegramException;
use TelegramSDK\BotAPI\Exception\InvalidTokenException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;


/**
 * Telegram Bot class.
 * This class provides methods to interact with the Telegram Bot API, send requests, and retrieve updates.
 *
 * @author Sebastiano Racca
 * @package TelegramSDK\BotAPI\Telegram
 * @link https://botapi.racca.me/docs/usage/general
 */
class Bot
{
    private string $token;
    private int $updatesMethod;
    private string $apiURL = "";

    public const DEFAULT_API_URL = "https://api.telegram.org/bot";

    /**
     * Bot constructor.
     *
     * @param string $token The Telegram bot token.
     * @param int|null $updatesMethod The updates method to use.
     * @param string $apiURL The API URL for Telegram requests.
     *
     * @throws \InvalidArgumentException If an invalid updates method is provided.
     */
    public function __construct(string $token, ?int $updatesMethod = null, string $apiURL = self::DEFAULT_API_URL)
    {
        $this->token = $token;
        $this->updatesMethod = $updatesMethod ?? -1; // No update method, will throw an exception on $this->updates()
        $this->apiURL = $apiURL;
    }

    /**
     * Checks if the provided token is valid.
     *
     * @param bool $thoroughCheck If false, performs a superficial check; otherwise, verifies with the Telegram API.
     *
     * @return bool True if the token is valid; otherwise, false.
     */
    public function isValidToken(bool $thoroughCheck): bool
    {
        if (!preg_match('/[0-9]+:[A-Za-z0-9]+/', $this->token)) {
            return false;
        }

        if (!$thoroughCheck) {
            return true;
        }

        try {
            return $this->getMe()->getBody()->ok;
        } catch (TelegramException $e) {
            return false;
        }
    }

    /**
     * Sends a request to the Telegram API.
     *
     * @param string $method The method to call.
     * @param array|object|null $arguments The arguments for the method.
     * @param int $timeout The request timeout.
     *
     * @return TelegramResponse The response from the Telegram API or null on RequestException.
     *
     * @throws TelegramException If an error occurs during the request (in non-production mode).
     */
    protected function sendRequest(string $method, array|object|null $arguments = null, $timeout = 10): TelegramResponse
    {
        $telegramUrl = $this->apiURL . $this->token . "/$method";
        $client = new GuzzleClient(['timeout' => $timeout]);

        try {
            $options = [];

            if (!empty($arguments)) {
                if (is_array($arguments)) {
                    $options['form_params'] = $arguments;
                } else {
                    $options['json'] = $arguments;
                }
            }

            $response = $client->post($telegramUrl, $options);

            $body = json_decode($response->getBody()->getContents());

            return new TelegramResponse($body, $response->getStatusCode(), null);

        } catch (RequestException $e) {
            $response = $e->getResponse();

            throw new TelegramException(
                $e->getMessage(),
                $response ? $response->getStatusCode() : 400,
                $response ? json_decode($response->getBody()->getContents()) : null
            );
        }
    }

    /**
     * Retrieves updates from the Telegram API.
     *
     * @param int|null $offset The updates offset, only in UPDATES_FROM_WEBHOOK mode.
     *
     * @return Update|null The retrieved updates, null on NO_UPDATES mode.
     */
    public function updates(?int $offset = null): ?Update
    {
        if ($this->updatesMethod === Update::UPDATES_FROM_GET_UPDATES) {
            return new Update($this->getUpdates([
                "offset" => isset($offset) ? $offset + 1 : null
            ])->getBody(), $this->updatesMethod);
        }

        if ($this->updatesMethod === Update::UPDATES_FROM_WEBHOOK) {
            return new Update(json_decode(file_get_contents("php://input")), $this->updatesMethod);
        }

        return null;
    }

    /**
     * Magic method for dynamically calling API methods.
     *
     * @param string $method The method to call.
     * @param array $arguments The arguments for the method.
     *
     * @return TelegramResponse The response from sendRequest().
     */
    public function __call($method, $arguments): mixed
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }

        return $this->sendRequest($method, ...$arguments);
    }
}
