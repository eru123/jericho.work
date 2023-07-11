<?php

// show all errors
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use eru123\config\DotEnv;
use App\Plugin\Domain;

DotEnv::load(__DIR__);

$domain = Domain::instance(__DIR__ . '/App/domains');
$domain->createVirtualHost(env('DOMAIN'), 'main');
$domain->createVirtualHost(env('CDN_DOMAIN'), 'cdn');

$domain->serve();
