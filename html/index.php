<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Domain;

$domain = Domain::instance(__DIR__ . '/App/domains');
$domain->createVirtualHost(env('DOMAIN'), 'main');
$domain->createVirtualHost(env('CDN_DOMAIN'), 'cdn');
$domain->createVirtualHost(env('ADMIN_DOMAIN'), 'main');

$domain->serve();
