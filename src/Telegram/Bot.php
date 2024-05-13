<?php

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

use TelegramSDK\BotAPI\Exception\TelegramException;
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
    protected string $token;
    private int $updatesMethod;
    private string $apiURL;
    private bool $payload;

    public const DEFAULT_API_URL = "https://api.telegram.org/";

    /**
     * Bot constructor.
     *
     * @param string $token The Telegram bot token.
     * @param int|null $updatesMethod The updates method to use.
     * @param string $apiURL The API URL for Telegram requests.
     *
     * @throws \InvalidArgumentException If an invalid updates method is provided.
     */
    public function __construct(
        string $token,
        ?int $updatesMethod = null,
        string $apiURL = self::DEFAULT_API_URL,
        bool $replyWithPayload = false
    ) {
        $this->token = $token;
        $this->updatesMethod = $updatesMethod ?? -1; // No update method, will throw an exception on $this->updates()
        $this->apiURL = $apiURL;
        $this->asPayload($replyWithPayload);
    }

    /**
     * Gets the api url.
     *
     * @return string The url.
     */
    public function getApiUrl(): string {
        return $this->apiURL;
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
            return $this->getMe()->body->ok;
        } catch (TelegramException $e) {
            return false;
        }
    }

    /**
     * Replies directly to the webhook update with a payload in the body.
     *
     * @param string $method The API method.
     * @param array|object $arguments The arguments for the method.
     *
     * @return void
     */
    protected function replyAsPayload(string $method, array|object $arguments = []): void
    {
        $payload = json_encode(['method' => $method, ...$arguments], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($payload));
        echo $payload;

        fastcgi_finish_request();
    }

    /**
     * Download a file from the server.
     *
     * @param string $path The file_path given by /getFile.
     * @param string $destination The destination of the file to be downloaded.
     * @param int $timeout The request timeout.
     *
     * @return bool Wheter the download was successfull or not.
     */
    public function downloadFile(string $path, string $destination, $timeout = 10) {
        try {
            $client = new GuzzleClient([
                'timeout' => $timeout,
                'stream' => true,
                'sink' => $destination,
            ]);
            $client->request('GET', $this->apiURL . 'file/bot' . $this->token . "/$path");

            return true;
        } catch(RequestException $e) {
            unlink($destination);
            return false;
        }
    }

    /**
     * Sends a request to the Telegram API.
     *
     * @param string $method The method to call.
     * @param array|object|null $arguments The arguments for the method.
     * @param int $timeout The request timeout.
     * @param bool $multipart Pass true to use 'multipart/form-data', false to use 'application/json'.
     *
     * @return TelegramResponse The response from the Telegram API or null on RequestException.
     *
     * @throws TelegramException If an error occurs during the request.
     */
    protected function sendRequest(string $method, array|object|null $arguments = null, $timeout = 10, bool $multipart = false): TelegramResponse
    {
        $telegramUrl = $this->apiURL . 'bot' . $this->token . "/$method";
        $client = new GuzzleClient(['timeout' => $timeout]);

        try {
            $options = [];

            if (!empty($arguments)) {
                $options[$multipart ? 'multipart' : 'json'] = $arguments;
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
            ])->body, $this->updatesMethod);
        }

        if ($this->updatesMethod === Update::UPDATES_FROM_WEBHOOK) {
            return new Update(json_decode(file_get_contents("php://input")), $this->updatesMethod);
        }

        return null;
    }

    /**
     * Sets how to reply to the Telegram API.
     * @param bool $enablePayload Set to true to send a payload insted of another request, false to send a request.
     * @return void
     */
    public function asPayload(bool $enablePayload = true): void
    {
        $this->payload = $enablePayload;

        if($enablePayload) {
            if($this->updatesMethod !== Update::UPDATES_FROM_WEBHOOK || !function_exists('fastcgi_finish_request')) {
                throw new \LogicException("Can't send payload on response if php-fpm isn't enabled");
            }
        }
    }

    /**
     * Magic method for dynamically calling API methods.
     *
     * @param string $method The API method.
     * @param array $arguments The arguments for the method.
     *
     * @return TelegramResponse|null The response from sendRequest() or null if the API call was sent as a payload.
     */
    public function __call($method, $arguments): mixed
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }

        if(!$this->payload) {
            return $this->sendRequest($method, ...$arguments);
        }

        $this->replyAsPayload($method, ...$arguments);
        return null;
    }
}
