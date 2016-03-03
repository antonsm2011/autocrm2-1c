<?php
/** @var \Silex\Application $app */

use Silex\Provider\MonologServiceProvider;

define('CONTROLLERS_DIR', __DIR__ . '/../controllers');

date_default_timezone_set('Europe/Moscow');

$app->register(new MonologServiceProvider(), [
    "monolog.logfile" => __DIR__ . "/../logs/" . date("Y-m-d") . ".log",
    "monolog.level" => isset($app["log.level"]) ? $app["log.level"] : 'WARNING',
    "monolog.name" => "application"
]);

$app->error(function (\Exception $e, $code) use ($app) {
    $app['monolog']->addError($e->getMessage());
    $app['monolog']->addError($e->getTraceAsString());

    return ['error_code' => $code, 'error_message' => $e->getMessage()];
});
