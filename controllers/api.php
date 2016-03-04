<?php
/** @var \Silex\Application $app */

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @var \Silex\ControllerCollection $api */
$api = $app['controllers_factory'];

$api->post('/package/create', function (Request $request) use ($app) {
    /** @var PDO $db */
    $db = $app['db'];

    $dbData = [
        ':client' => $app['client']['id'],
        ':data' => json_encode($request->get('data'), JSON_UNESCAPED_UNICODE)
    ];

    $sql = 'insert into packages (created_by, created_at, data) values (:client, now(), :data)';

    return $db->prepare($sql)->execute($dbData)
        ? new Response($request->getContent(), 201, ['Content-Type' => $request->headers->get('Content-Type')])
        : new Response('Ошибка обработки данных', 500);
});


// settings

$api->before(function (Request $request) use ($app) {
    $key = $request->headers->get('X-API-Key');

    if (!$key) {
        $app->abort(401, 'Не указан API ключ');
    }

    if (!isset($app['clients_keys'][$key])) {
        $app->abort(403, 'Указан недействительный API ключ');
    }

    $app['client'] = $app['clients_keys'][$key];
});

$api->before(function (Request $request) use ($app) {
    //accepting JSON
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $app->abort(400, 'Данные форматированы некорректно');
        }
        $request->request->set('data', $data);
    }
});

$app->view(function (array $controllerResult, Request $request) use ($app) {
    return substr($request->getRequestUri(), 0, 5) == '/api/' ? $app->json($controllerResult) : $controllerResult;
});

return $api;
