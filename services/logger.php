<?php
/** @var \Silex\Application $app */

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Silex\Provider\MonologServiceProvider;

$app->register(new MonologServiceProvider(), [
    "monolog.logfile" => __DIR__ . "/../logs/" . date("Y-m-d") . ".log",
    "monolog.level" => isset($app["log.level"]) ? $app["log.level"] : 'WARNING',
    "monolog.name" => "application"
]);

$app->extend('monolog', function (Logger $service) use ($app) {
    return $service->pushProcessor(function ($record) use ($app) {
        return array_merge($record, ['process_id' => $app['process_id']]);
    });
});

$app->extend('monolog.handler', function (HandlerInterface $service) use ($app) {
    return $service->setFormatter(
        new LineFormatter("%datetime% [%process_id%] %channel%.%level_name%: %message% %context% %extra%\n")
    );
});
