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

$app['clients_keys'] = $app->protect(function ($key) use ($app) {
    foreach ($app['clients'] as $client) {
        foreach ($client['keys'] as $clientKey) {
            if ($key == $clientKey)  {
                return $client;
            }
        }
    }

    return null;
});
