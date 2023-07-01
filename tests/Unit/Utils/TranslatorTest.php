<?php

use TelegramSDK\BotAPI\Utils\Translator;

it('translates a string', function () {
    $translator = new Translator((object)[
        'hello' => 'Hello',
        'world' => 'World',
    ], [
        'Hello' => 'Bonjour',
        'World' => 'Monde',
    ]);

    expect($translator->translate('Hello, World'))->toBe('Bonjour, Monde');
});

it('translates a property value', function () {
    // Create an instance of the Translator class
    $translator = new Translator((object)[
        'greeting' => 'Hello, World',
    ], [
        'Hello' => 'Bonjour',
        'World' => 'Monde',
    ]);

    expect($translator->greeting)->toBe('Bonjour, Monde');
});

it('returns null for non-existing property', function () {
    $translator = new Translator((object)[
        'hello' => 'Hello',
        'world' => 'World',
    ]);

    expect($translator->nonExistingProperty)->toBeNull();
});
