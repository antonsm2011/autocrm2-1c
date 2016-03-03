<?php
/** @var \Silex\Application $app */

/** @var \Silex\ControllerCollection $root */
$root = $app['controllers_factory'];

$root->get('/', function () {
    return 'hello world!!!';
});

return $root;
