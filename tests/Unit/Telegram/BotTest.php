<?php

use TelegramSDK\BotAPI\Telegram\Bot;
use TelegramSDK\BotAPI\Exceptions\TelegramException;


it("throws an exception on invalid token in non-production environment", function () {
    $this->expectException(TelegramException::class);

    $bot = new Bot("an invalid bot token");
});

it("doens't throw an exception on invalid token in production environment", function () {
    if(defined("PRODUCTION"))
        runkit7_constant_redefine("PRODUCTION", true);
    else
        define("PRODUCTION", true);

    expect(new Bot("an invalid bot token"))
        ->toBeInstanceOf(Bot::class);
});