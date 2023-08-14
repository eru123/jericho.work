<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\DB;

$loaded = extension_loaded('pdo') && extension_loaded('pdo_mysql');
echo "[Test] " . ($loaded ? 'OK PDO/MySQL extension loaded' : 'FAILED PDO/MySQL extension not loaded') . PHP_EOL;

if (!$loaded) {
    exit;
}

$tables = [
    'cdn',
    'envs',
    'mails',
    'mail_templates',
    'smtps',
    'users',
    'verifications',
];

$passed = 0;
try {
    $db = DB::instance();
    foreach ($tables as $table) {
        $rows = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "[Test] OK PDO/MySQL $table has $rows rows", PHP_EOL;
        $passed++;
    }
} catch (Exception $e) {
    echo "[Test] FAILED PDO/MySQL " . $e->getMessage() . PHP_EOL;
    exit;
}
