<?php

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Telegram;

use TelegramSDK\BotAPI\Utils;
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


    public function __construct(string $token, int $updatesMethod = 0){
        $this->token = $token;
        $this->isProduction = Utils::isProduction();
        $this->updatesMethod = $updatesMethod;

        if(!$this->isProduction){ // Assuming that you've tested the bot before pushing to production
            $this->getMe(); // Throws a TelegramException on invalid token
            $this->isValidUpdatesMethod(); // Throws InvalidArgumentException on invalid update method
        }

    }

    private function isValidUpdatesMethod(): void{
        if(!($this->updatesMethod >= 0 && $this->updatesMethod <= 2))
            throw new \InvalidArgumentException("Invalid updates method.");
    }

    private function sendRequest(string $method, array|object|null $arguments = null, $timeout = 10): ?TelegramResponse{
        $telegram_url = "https://api.telegram.org/bot" . $this->token . "/$method";
        $client = new Guzzle(['timeout' => $timeout]);

        try{

            $response = $client->post($telegram_url, ['form_params' => $arguments]);
            $stream = $response->getBody();
            $response->body = json_decode($stream->getContents());
            $stream->close();

            return new TelegramResponse([
                "statusCode" => $response->getStatusCode(),
                "body" => $response->body
            ]);

        } catch(RequestException $e){
            if(!$this->isProduction)
                throw new TelegramException($e->getMessage());

            error_log($e->getMessage());

            return null;
        }
    }

    public function updates(?int $offset = null): ?Updates{
        if($this->updatesMethod === self::UPDATES_FROM_GET_UPDATES){

            return new Updates($this->getUpdates([
                "offset" => $offset + 1
            ])->body);

        }else if($this->updatesMethod === self::UPDATES_FROM_WEBHOOK){

            return new Updates(json_decode(file_get_contents("php://input")));

        } else{

            return NULL;

        }
    }

    public function __call($method, $arguments): ?TelegramResponse{
        return $this->sendRequest($method, ...$arguments);
    }
}
