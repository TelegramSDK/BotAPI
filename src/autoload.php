<?php

declare(strict_types=1);

define("TELEGRAM_SDK_ROOT_DIR", dirname(__DIR__));


function TelegramSDKAutoloader(string $className): void{
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

    if(file_exists($file))
        require_once $file;
}

spl_autoload_register('TelegramSDKAutoloader');

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/', RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file)
    if ($file->isFile() && $file->getExtension() === 'php')
        require_once $file->getPathname();