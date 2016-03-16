<?php
/** @var \Silex\Application $app */

$app['association_fetcher'] = $app->protect(function ($clientId, $type, $sourceId) use ($app) {
    /** @var \Monolog\Logger $logger */
    $logger = $app['logger']->withName('association_fetcher');
    /** @var PDO $db */
    $db = $app['db'];

    $sql = 'select crm_id from associations where client = :clientId and type = :type and source_id = :sourceId';
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':clientId' => $clientId,
        ':type' => $type,
        ':sourceId' => $sourceId,
    ]);

    if ($crmId = $stmt->fetchColumn()) {
        $logger->debug(sprintf('Найдена ассоциация типа "%s": "%s" => "%s"', $type, $sourceId, $crmId));
    } else {
        $logger->debug(sprintf('Не найдена ассоциация типа "%s" для ID = "%s"', $type, $sourceId));
    }
    
    return $crmId;
});

$app['association_saver'] = $app->protect(function ($clientId, $type, $sourceId, $crmId) use ($app) {
    /** @var PDO $db */
    $db = $app['db'];

    $sql = 'insert into associations (client, type, source_id, crm_id) values (:clientId, :type, :sourceId, :crmId)';
    $stmt = $db->prepare($sql);
    
    return $stmt->execute([
        ':clientId' => $clientId,
        ':type' => $type,
        ':sourceId' => $sourceId,
        ':crmId' => $crmId,
    ]);
});
