<?php

use TelegramSDK\BotAPI\Utils;

it("returns false when PRODUCTION constant is not defined", function () {
    if(defined("PRODUCTION"))
        runkit7_constant_remove("PRODUCTION");

    expect(Utils::isProduction())->toBeFalse();
});

it("returns true when PRODUCTION constant is defined and true", function () {
    if(defined("PRODUCTION"))
        runkit7_constant_redefine("PRODUCTION", true);
    else
        define("PRODUCTION", true);


    expect(Utils::isProduction())->toBeTrue();
});

it("returns false when PRODUCTION constant is defined and false", function () {
    if(defined("PRODUCTION"))
        runkit7_constant_redefine("PRODUCTION", false);
    else
        define("PRODUCTION", false);

    expect(Utils::isProduction())->toBeFalse();
});