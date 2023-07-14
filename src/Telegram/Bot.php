<?php
/**
 * Telegram Bot class.
 * This class provides methods to send requests to Telegram or get the updates.
 *
 * @author Sebastiano Racca
 * @package TelegramSDK\BotAPI\Telegram
 * @see docs/00-introduction.md
 * @see docs/01-updates.md
 */

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

use TelegramSDK\BotAPI\Exceptions\TelegramException;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;

class Bot{
    protected string $token;
    public int $updatesMethod;
    private bool $isProduction;

    public const NO_UPDATES = 0;
    public const UPDATES_FROM_WEBHOOK = 1;
    public const UPDATES_FROM_GET_UPDATES = 2;


    /**
     * Bot constructor.
     *
     * @param string $token                The Telegram bot token.
     * @param int $updatesMethod           The updates method to use.
     *
     * @throws TelegramException           If an the provided token is invalid (in non-production mode).
     * @throws \InvalidArgumentException   If an invalid updates method is provided (in non-production mode).
     */
    public function __construct(string $token, int $updatesMethod = self::NO_UPDATES){
        $this->token = $token;
        $this->isProduction = \TelegramSDK\Utils\isProduction();
        $this->updatesMethod = $updatesMethod;

        if(!$this->isProduction){ // Assuming that you've tested the bot before pushing to production
            $this->getMe(); // Throws a TelegramException on invalid token
            $this->isValidUpdatesMethod(); // Throws InvalidArgumentException on invalid update method
        }

    }

    /**
     * Checks if the provided updates method is valid.
     *
     * @throws \InvalidArgumentException If the updates method is invalid.
     */
    private function isValidUpdatesMethod(): void{
        if(!($this->updatesMethod >= 0 && $this->updatesMethod <= 2))
            throw new \InvalidArgumentException("Invalid updates method.");
    }

    /**
     * Sends a request to the Telegram API.
     *
     * @param string $method                 The method to call.
     * @param array|object|null $arguments   The arguments for the method.
     * @param int $timeout                   The request timeout.
     *
     * @return TelegramResponse              The response from the Telegram API or null on RequestException.
     *
     * @throws TelegramException             If an error occurs during the request (in non-production mode).
     */
    private function sendRequest(string $method, array|object|null $arguments = null, $timeout = 10): TelegramResponse{
        $telegram_url = "https://api.telegram.org/bot" . $this->token . "/$method";
        $client = new Guzzle(['timeout' => $timeout]);

        try{

            $response = $client->post($telegram_url, ['form_params' => $arguments]);
            $stream = $response->getBody();
            $response->body = json_decode($stream->getContents());
            $stream->close();

            return new TelegramResponse([
                "statusCode" => $response->getStatusCode(),
                "body" => $response->body,
                "error" => null
            ]);

        } catch(RequestException $e){
            if(!$this->isProduction)
                throw new TelegramException($e->getMessage());

            $response = $e->getResponse();

            return new TelegramResponse([
                "statusCode" => $response->getStatusCode() ?? null,
                "body" => $response->getBody()->getContents() ?? null,
                "error" => $e
            ]);
        }
    }

    /**
     * Retrieves updates from the Telegram API.
     *
     * @param bool $enableDefaultUpdates   Whether the default updates should be enabled or not.
     * @param int|null $offset             The updates offset, only in UPDATES_FROM_WEBHOOK mode.
     *
     * @return Updates|null                The retrieved updates, null on NO_UPDATES mode.
     */
    public function updates(bool $enableDefaultUpdates = false, ?int $offset = null): ?Updates{
        if($this->updatesMethod === self::UPDATES_FROM_GET_UPDATES){

            return new Updates($this->getUpdates([
                "offset" => isset($offset) ? $offset + 1 : null
            ])->body, $enableDefaultUpdates);

        }else if($this->updatesMethod === self::UPDATES_FROM_WEBHOOK){

            return new Updates(json_decode(file_get_contents("php://input")), $enableDefaultUpdates);

        } else{

            return NULL;

        }
    }

    /**
     * Magic method for dynamically calling API methods.
     *
     * @param string $method            The method to call.
     * @param array $arguments          The arguments for the method.
     *
     * @return TelegramResponse         The response from sendRequest().
     */
    public function __call($method, $arguments): TelegramResponse{
        return $this->sendRequest($method, ...$arguments);
    }
}
