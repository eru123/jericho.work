<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\Domain;

$domain = Domain::instance(__DOMAINS__);
$domain->setDefaultRecord('main');
$domain->serve();
