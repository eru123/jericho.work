<?php

require_once __DIR__ . '/autoload.php';

try {
    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', 3306);
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');
    $name = env('DB_NAME', 'main');

    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $stmt->execute();

    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :name");
    $stmt->execute(['name' => $name]);
    $result = $stmt->fetch();
    if ($result) {
        echo "[Migration] OK Database created", PHP_EOL;
    } else {
        echo "[Migration] OK Database still does not exist.", PHP_EOL;
        exit(1);
    }
} catch (Exception $e) {
    echo "[Migration] FAILED ", $e->getMessage(), PHP_EOL;
}
