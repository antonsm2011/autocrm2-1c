<?php

use Silex\Application;
use Symfony\Component\Debug\ErrorHandler;

require_once __DIR__.'/../vendor/autoload.php';

$errorHandler = ErrorHandler::register();

$app = new Silex\Application();

require __DIR__ . '/../config/config.php';

$app->extend('logger', function ($logger) use ($errorHandler) {
    $errorHandler->setDefaultLogger($logger);

    return $logger;
});

foreach (scandir(CONTROLLERS_DIR) as $file) {
    if (is_file(CONTROLLERS_DIR . '/'. $file) && substr($file, -4) === '.php') {
        $section = substr($file, 0, -4);
        $app->mount('/' . ($section == '_root' ? '' : $section), include CONTROLLERS_DIR . '/' . $file);
    }
}

$app->run();
