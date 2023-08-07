<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Domain;

$domain = Domain::instance(__DOMAINS__);
$domain->setDefaultHandler('main');
!env('DOMAIN') || $domain->createVirtualHost(env('DOMAIN'), 'main');
!env('CDN_DOMAIN') || $domain->createVirtualHost(env('CDN_DOMAIN'), 'main');
!env('ADMIN_DOMAIN') || $domain->createVirtualHost(env('ADMIN_DOMAIN'), 'main');
!env('JERICHO_PORTFOLIO_DOMAIN') || $domain->createVirtualHost(env('JERICHO_PORTFOLIO_DOMAIN'), 'jericho-portfolio');
$domain->serve();
