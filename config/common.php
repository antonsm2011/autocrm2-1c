<?php
/** @var \Silex\Application $app */

define('CONTROLLERS_DIR', __DIR__ . '/../controllers');
define('SERVICES_DIR', __DIR__ . '/../services');

date_default_timezone_set('Europe/Moscow');

$app['process_id'] = $app->share(function () {
    return md5(getmypid() . microtime());
});

foreach (scandir(SERVICES_DIR) as $file) {
    if (is_file(SERVICES_DIR . '/' . $file) && substr($file, -4) === '.php') {
        include SERVICES_DIR . '/' . $file;
    }
}
