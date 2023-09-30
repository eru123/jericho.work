<?php

define('__CWD__', __DIR__);
define('__BIN__', __DIR__ . DIRECTORY_SEPARATOR . 'bin');
define('__APP__', __DIR__ . DIRECTORY_SEPARATOR . 'App');
define('__LOGS__', __DIR__ . DIRECTORY_SEPARATOR . 'logs');
define('__ROUTES__', __DIR__ . DIRECTORY_SEPARATOR . 'routes');
define('__CLIENT__', __DIR__ . DIRECTORY_SEPARATOR . 'client');
define('__SCRIPTS__', __DIR__ . DIRECTORY_SEPARATOR . 'scripts');
define('__DOMAINS__', __DIR__ . DIRECTORY_SEPARATOR . 'domains');
define('__COMMANDS__', __DIR__ . DIRECTORY_SEPARATOR . 'Commands');
define('__DATABASE__', __DIR__ . DIRECTORY_SEPARATOR . 'database');
define('__SEEDS__', __DATABASE__ . DIRECTORY_SEPARATOR . 'seeds');
define('__MIGRATIONS__', __DATABASE__ . DIRECTORY_SEPARATOR . 'migrations');
define('__MAILTPL__', __DIR__ . DIRECTORY_SEPARATOR . 'mailtpl');

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

if (php_sapi_name() == 'cli') {
    // list all classes that uses the App\Commands\Command class
    $classes = get_declared_classes();
}