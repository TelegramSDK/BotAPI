<?php

declare(strict_types=1);

namespace TelegramSDK\BotAPI;


class Utils{

    /**
     * Checks if the application is running in production mode.
     * 
     * @return bool True if in production mode, false otherwise.
     */
    public static function isProduction(): bool{
        return (defined('\PRODUCTION') && \PRODUCTION === true);
    }
}