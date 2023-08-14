<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\MC;

$ext_loaded = extension_loaded('memcached');
echo "[Test] " . ($ext_loaded ? 'OK Memcached extension loaded' : 'FAILED Memcached extension not loaded') . PHP_EOL;

$class_loaded = class_exists('Memcached');
echo "[Test] " . ($class_loaded ? 'OK Memcached class loaded' : 'FAILED Memcached class not loaded') . PHP_EOL;

if (!$ext_loaded && !$class_loaded) {
    echo "[Test] FAILED No Memcached extension or class loaded", PHP_EOL;
    exit;
}

$mc = MC::instance();
$test_count = 10;
$last_code = null;
$passed = 0;
$identifier = 'test_mc:code';
for ($i = 0; $i < $test_count + 1; $i++) {
    if ($i != 0 && $last_code == $mc->get($identifier) && $last_code) {
        $passed++;
        if ($i == $test_count) break;
    }

    $code = rand(100000, 999999);
    $mc->set($identifier, $code, 1);
    $last_code = $code;
}

echo "[Test] OK Memcached passed $passed out of $test_count tests", PHP_EOL;