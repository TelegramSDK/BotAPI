<?php

use TelegramSDK\BotAPI\Telegram\Updates;

it("sets lastUpdateID correctly when data contains result", function () {
    $data = (object) [
        "ok" => true,
        "result" => [
            (object)["update_id" => 1],
            (object)["update_id" => 2],
            (object)["update_id" => 3],
        ],
    ];

    $updates = new Updates($data);

    expect($updates->lastUpdateID)->toBe(3);
});

it("sets lastUpdateID to null when data does not contain result", function () {
    $data = (object)[
        "ok" => true,
        "result" => [

        ]
    ];

    $updates = new Updates($data);

    expect($updates->lastUpdateID)->toBeNull();
});