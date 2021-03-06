<?php
/** @var \Silex\Application $app */

$app['db'] = $app->share(function ($app) {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        isset($app['db.host']) ? $app['db.host'] : 'localhost',
        isset($app['db.port']) ? $app['db.port'] : '3306',
        $app['db.name']
    );
    $user = isset($app['db.user']) ? $app['db.user'] : 'root';
    $password = isset($app['db.password']) ? $app['db.password'] : '';

    $connection = new PDO($dsn, $user, $password);
    $connection->exec('SET NAMES utf8');
    $connection->exec('SET time_zone = "' . date('P') . '"');

    return $connection;
});
