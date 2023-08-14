<?php

require_once __DIR__ . '/autoload.php';

// check if required applications are installed for frontend development
$required = [
    'node',
    'npm',
    'pnpm',
];

$dists = [
    'cdn',
    'main',
    'admin',
];

$not_installed = 0;
if (PHP_OS_FAMILY == 'Windows') {
    foreach ($required as $app) {
        $output = cmd(['where', $app]);
        if (strpos($output, 'Could not find files') !== false) {
            $not_installed++;
            echo "[Test] FAILED FE $app not installed", PHP_EOL;
        }
    }
} else {
    foreach ($required as $app) {
        $output = cmd(['which', $app]);
        if (strpos($output, 'not found') !== false || empty(trim($output))) {
            $not_installed++;
            echo "[Test] FAILED FE $app not installed", PHP_EOL;
        }
    }
}

echo "[Test] " . (empty($not_installed) ? 'OK' : 'FAILED') . " FE " . (count($required) - $not_installed) . " out of " . count($required) . " required applications installed", PHP_EOL;

$no_dist = 0;
foreach ($dists as $dist) {
    $dist_path = realpath(__CLIENT__ . DIRECTORY_SEPARATOR . $dist . DIRECTORY_SEPARATOR . 'dist');
    if (!$dist_path || !is_dir($dist_path)) {
        $no_dist++;
        echo "[Test] FAILED FE dist $dist not found", PHP_EOL;
    }
}

echo "[Test] " . (empty($no_dist) ? 'OK' : 'FAILED') . " FE " . (count($dists) - $no_dist) . " out of " . count($dists) . " dists found", PHP_EOL;
