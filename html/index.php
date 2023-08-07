<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Domain;

$domain = Domain::instance(__DOMAINS__);
$domain->setDefaultHandler('main');
$domain->createVirtualHost(env('DOMAIN'), 'main');
$domain->createVirtualHost(env('CDN_DOMAIN'), 'main');
$domain->createVirtualHost(env('ADMIN_DOMAIN'), 'main');
$domain->createVirtualHost(env('JERICHO_PORTFOLIO_DOMAIN'), 'jericho-portfolio');
$domain->serve();
