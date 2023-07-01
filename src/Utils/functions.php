<?php
/**
 * This file provides a set of util functions for the library.
 *
 * @author Sebastiano Racca
 * @package TelegramSDK\BotAPI\Utils
 */

declare(strict_types=1);

namespace TelegramSDK\BotAPI\Utils;


/**
 * Checks if the application is running in production mode.
 *
 * @return bool True if in production mode, false otherwise.
 */
function isProduction(): bool{
    return (defined('\PRODUCTION') && \PRODUCTION === true);
}