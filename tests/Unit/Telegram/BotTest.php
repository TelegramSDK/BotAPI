<?php

use TelegramSDK\BotAPI\Telegram\Bot;
use TelegramSDK\BotAPI\Exception\TelegramException;

it("returns false on invalid token syntax", function () {
    $bot = new Bot("an invalid bot token");
    expect($bot->isValidToken(false))->toBeFalse();
});

it("returns false on invalid token", function () {
    $bot = new Bot("123:abc");
    expect($bot->isValidToken(true))->toBeFalse();
});

it("throws an exception on invalid token", function () {
    $this->expectException(TelegramException::class);

    $bot = new Bot("an invalid bot token");
    $bot->getMe();
});
