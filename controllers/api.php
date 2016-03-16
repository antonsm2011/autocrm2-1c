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

    if ($db->prepare($sql)->execute($dbData)) {
        return new Response($request->getContent(), 201, ['Content-Type' => $request->headers->get('Content-Type')]);
    } else {
        $app['logger']->error('Ошибка при сохранении данных', [
            'sql' => $sql,
            'params' => $dbData,
            'error' => $db->errorInfo()
        ]);

        return new Response('Ошибка при сохранении данных', 500);
    }
});


// settings

$app->finish(function () use ($app) {
    $retryMargin = date_create()->modify('-5 minutes');

    /** @var \Monolog\Logger $logger */
    $logger = $app['logger']->withName('v2_interaction');
    $logger->info('Начало обработки отложенных записей не обрабатывавшихся с ' . $retryMargin->format('d.m.Y H:i:s'));

    /** @var PDO $db */
    $db = $app['db'];
    $recordsCount = $db->exec(
        'update packages set locked_by = ' . $db->quote($app['process_id'])
            . ' where locked_by is null and finished_at is null and ifnull(processed_at, "0000-00-00 00:00:00") < '
            . $db->quote($retryMargin->format('Y-m-d H:i:s'))
    );

    if (!$recordsCount) {
        $logger->info('Очередь обработки пуста');

        return;
    }

    $logger->info('Количество записей, ожидающих обработки: ' . $recordsCount);

    $res = $db->query('select id, created_by, data from packages where locked_by = ' . $db->quote($app['process_id']));

    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        if (null === $data = json_decode($row['data'], true)) {
            $logger->error('Ошибка декодирования данных отложенной записи', ['row' => $row]);
        }
        $idSql = $db->quote($row['id'], PDO::PARAM_INT);

        $db->exec('update packages set processed_at = ' . $db->quote(date('Y-m-d H:i:s')) . ' where id = ' . $idSql);

        if ($app['v2']['service_case']($data, $row['created_by'])) {
            $db->exec(
                'update packages set finished_at = ' . $db->quote(date('Y-m-d H:i:s')) . ', locked_by = NULL'
                    . ' where id = ' . $idSql
            );
        } else {
            $logger->error('Ошибка при сохранении в v2 отложенной записи', ['row' => $row]);
        }
    }

    $logger->info('Обработка отложенных записей завершена');
});

$app->finish(function () use ($app) {
    /** @var PDO $db */
    $db = $app['db'];

    $db->exec('update packages set locked_by = NULL where locked_by = ' . $db->quote($app['process_id']));
});

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
