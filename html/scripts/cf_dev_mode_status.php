<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\CF;

$msg = CF::dev_mode() ? 'Cloudflare dev mode ON' : 'Cloudflare dev mode OFF';
echo $msg, PHP_EOL;

writelog($msg);
