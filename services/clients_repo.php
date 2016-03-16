<?php
/** @var \Silex\Application $app */


$app['clients'] = $app->share(function () {
    return require __DIR__ . '/../config/clients.php';
});

$app['client_fetcher'] = $app->protect(function ($id) use ($app) {
    $result = null;

    foreach ($app['clients'] as $client) {
        if ($client['id'] == $id) {
            $result = $client;
            break;
        }
    }

    return $result;
});

$app['clients_keys'] = $app->share(function ($app) {
    $map = [];
    foreach ($app['clients'] as $client) {
        foreach ($client['keys'] as $key) {
            $map[$key] = &$client;
        }
    }

    return $map;
});
