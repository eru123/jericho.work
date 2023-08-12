<?php

define('__CWD__', __DIR__);
define('__CLIENT__', __DIR__ . '/client');
define('__APP__', __DIR__ . '/App');
define('__SCRIPTS__', __DIR__ . '/scripts');
define('__DOMAINS__', __DIR__ . '/domains');
define('__ROUTES__', __DIR__ . '/routes');
define('__LOGS__', __DIR__ . '/logs');

if (!class_exists('Memcached')) {
    include ("memcached.php");
}

require_once __DIR__ . '/vendor/autoload.php';
eru123\config\DotEnv::load(__CWD__);

if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $comh = ['X-Powered-By', 'Server'];
    foreach ($comh as $header) {
        header_remove($header);
    }
}
