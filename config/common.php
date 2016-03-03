<?php
/** @var \Silex\Application $app */

use Silex\Provider\MonologServiceProvider;

define('CONTROLLERS_DIR', __DIR__ . '/../controllers');

date_default_timezone_set('Europe/Moscow');

$app['db'] = function ($app) {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        isset($app['db.host']) ? $app['db.host'] : 'localhost',
        isset($app['db.port']) ? $app['db.port'] : '3306',
        $app['db.name']
    );
    $user = isset($app['db.user']) ? $app['db.user'] : 'root';
    $password = isset($app['db.password']) ? $app['db.password'] : '';

    return new PDO($dsn, $user, $password, [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET time_zone = "' . date('P') . '"'
    ]);
};

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
