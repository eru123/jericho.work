<?php

require_once __DIR__ . '/autoload.php';
use App\Plugin\CF;

echo CF::purge_cache() ? 'Cloudflare cache purged' : 'Cloudflare cache purge failed';
echo PHP_EOL;

writelog('Cloudflare cache purged');
