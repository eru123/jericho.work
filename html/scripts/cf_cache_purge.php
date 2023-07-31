<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\CF;

$msg = CF::purge_cache() ? 'Cloudflare cache purged' : 'Cloudflare cache purge failed';
echo $msg, PHP_EOL;

writelog($msg);
