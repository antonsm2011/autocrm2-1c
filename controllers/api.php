<?php
/** @var \Silex\Application $app */

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

const RETRY_SECONDS = 60;
const NEW_PROCESS_SECONDS = 10;

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
    $retryMargin = date_create()->modify('-' . RETRY_SECONDS . ' seconds');
    $newProcessMargin = date_create()->modify('-' . NEW_PROCESS_SECONDS . ' seconds');

    /** @var \Monolog\Logger $logger */
    $logger = $app['logger']->withName('v2_interaction');

    /** @var PDO $db */
    $db = $app['db'];

    $inProgressStatement = $db->prepare(
        'SELECT processed_by FROM packages WHERE status = "checked" and processed_at >= ? LIMIT 1'
    );
    if (!$inProgressStatement->execute([$newProcessMargin->format('Y-m-d H:i:s')])) {
        $logger->error('Ошибка БД при проверке того, запущена ли обработка', [
            'PDO error' => $inProgressStatement->errorInfo()
        ]);

        return;
    }
    $inProgress = $inProgressStatement->fetchColumn();

    if ($inProgress) {
        $logger->info('Обработку в этом процессе не начинаем, т.к. она уже идет в процессе "' . $inProgress . '".');

        return; // не запускаем параллельные обработки
    }

    $logger->info('Начало обработки добавленных отложенных записей');

    $processId = $app['process_id'];

    $checkStatement = $db->prepare(
        'UPDATE packages SET processed_by = ?, processed_at = now(), status = "checked" '
        . 'WHERE status = "new" OR status = "checked" and processed_at < ? ORDER BY created_at asc LIMIT 1'
    );
    $checkStatement->bindValue(1, $processId);
    $checkStatement->bindValue(2, $retryMargin->format('Y-m-d H:i:s'));
    $checkPackage = function () use ($checkStatement) {
        return $checkStatement->execute()
            ? ($checkStatement->rowCount() > 0 ? null : 'queue is empty')
            : 'check failed';
    };
    $getDataStatement = $db->prepare(
        'select id, created_by, data from packages where processed_by = ? and status = "checked"'
    );
    $getDataStatement->bindValue(1, $processId);
    $setStatusStatement = $db->prepare('update packages set status = ? where id = ?');

    $errorsCount = 0;
    $savedCount = 0;

    while (!$stopReason = $checkPackage()) {
        if (!$getDataStatement->execute()) {
            $stopReason = 'processing failed';
            break;
        }

        $row = $getDataStatement->fetch(PDO::FETCH_ASSOC);

        $data = json_decode($row['data'], true);

        if (null === $data) {
            $logger->error('Ошибка декодирования данных записи ' .  $row['id'], $row);
            $errorsCount++;
            continue;
        } elseif (empty($data['DataType']) || $data['DataType'] !== 'Заказ-наряд') {
            $logger->error('Данные записи ' .  $row['id'] . ' не являются заказ-нарядом');
            $errorsCount++;
            continue;
        }

        try {
            $caseId = $app['v2']['service_case']($data, $row['created_by']);
        } catch (Exception $e) {
            $caseId = null;
            $logger->error('Ошибка преобразования данных в формат CRM для ' .  $row['id'], ['exception' => $e]);
        }

        if (!$caseId) {
            $setStatusStatement->execute(['failed', $row['id']]);
            $logger->error('Ошибка при сохранении в v2 отложенной записи ' .  $row['id']);
            $errorsCount++;
        } else {
            $setStatusStatement->execute(['saved', $row['id']]);
            $logger->info('Запись ' . $row['id'] . ' успешно обработана');
            $savedCount++;
        }
    }

    switch ($stopReason) {
        case 'queue is empty':
            $logger->info($savedCount  + $errorsCount > 0
                ? 'Обработка отложенных записей завершена. '
                    . 'Успешно обработано - ' . $savedCount . ', '
                    . 'ошибок - ' . $errorsCount
                : 'Очередь обработки пуста'
            );
            break;
        case 'check failed':
            $logger->error('Ошибка при взятии данных в обработку', ['PDO error' => $checkStatement->errorInfo()]);
            break;
        case 'processing failed':
            $logger->error('Ошибка запроса данных отложенной записи', ['PDO error' => $getDataStatement->errorInfo()]);
            break;
        default:
            $logger->error('Неизвестная причина завершения цикла обработки ' . json_encode($stopReason) . '');
    }
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
            $app['logger']->error('Данные запроса форматированы некорректно', [
                'request' => [
                    'headers' => $request->headers->all(),
                    'body' => $request->getContent(),
                ],
            ]);

            return new Response('Данные запроса форматированы некорректно', 400);
        }
        $request->request->set('data', $data);
    }
});

$app->view(function (array $controllerResult, Request $request) use ($app) {
    return substr($request->getRequestUri(), 0, 5) == '/api/' ? $app->json($controllerResult) : $controllerResult;
});

return $api;
