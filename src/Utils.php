<?php

declare(strict_types=1);

namespace TelegramSDK\BotAPI;


class Utils{

    public static function isProduction(): bool{
        return (defined('\PRODUCTION') && \PRODUCTION === true);
    }
}