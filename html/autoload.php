<?php

define('__CWD__', __DIR__);
define('__CLIENT__', __DIR__ . DIRECTORY_SEPARATOR . 'client');
define('__APP__', __DIR__ . DIRECTORY_SEPARATOR . 'App');
define('__SCRIPTS__', __DIR__ . DIRECTORY_SEPARATOR . 'scripts');
define('__DOMAINS__', __DIR__ . DIRECTORY_SEPARATOR . 'domains');
define('__ROUTES__', __DIR__ . DIRECTORY_SEPARATOR . 'routes');
define('__LOGS__', __DIR__ . DIRECTORY_SEPARATOR . 'logs');
define('__DATABASE__', __DIR__ . DIRECTORY_SEPARATOR . 'database');
define('__MIGRATIONS__', __DATABASE__ . DIRECTORY_SEPARATOR . 'migrations');
define('__SEEDS__', __DATABASE__ . DIRECTORY_SEPARATOR . 'seeds');

if (!class_exists('Memcached') && PHP_OS_FAMILY == 'Windows') {
    include("memcached.php");
}

date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/vendor/autoload.php';
eru123\config\DotEnv::load(__CWD__);

if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $comh = ['X-Powered-By', 'Server'];
    foreach ($comh as $header) {
        header_remove($header);
    }
}
