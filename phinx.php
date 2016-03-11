<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();

require __DIR__ . '/config/config.php';

return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
        'seeds' => __DIR__ . '/fixtures',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'main',
        'main' => [
            'adapter' => 'mysql',
            'name' => $app['db.name'],
            'connection' => $app['db']
        ]
    ]
];
