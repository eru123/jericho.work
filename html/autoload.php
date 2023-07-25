<?php

define('__CWD__', __DIR__);
define('__CLIENT__', __DIR__ . '/client');
define('__APP__', __DIR__ . '/App');
define('__SCRIPTS__', __DIR__ . '/scripts');

require_once __DIR__ . '/vendor/autoload.php';
eru123\config\DotEnv::load(__CWD__);