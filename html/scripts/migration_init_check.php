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

    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :name");
    $stmt->execute(['name' => $name]);
    $result = $stmt->fetch();
    if ($result) {
        echo "[Migration] OK Database exists", PHP_EOL;
    } else {
        echo "[Migration] FAILED Database does not exist, attempting to create", PHP_EOL;
        echo cmd(['migration_init_db']);
    }

    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :name");
    $stmt->execute(['name' => $name]);
    $result = $stmt->fetch();

    if ($result) {
        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :name AND TABLE_NAME = 'migrations'");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch();

        if (!$result) {
            $stmt = $pdo->prepare("CREATE TABLE `{$name}`.`migrations` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `type` VARCHAR(255) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `migrations_name_unique` (`name` ASC) VISIBLE)
            ENGINE = InnoDB
            DEFAULT CHARACTER SET = utf8mb4
            COLLATE = utf8mb4_unicode_ci");
            $stmt->execute();
            echo "[Migration] OK Created migrations table", PHP_EOL;
        }

        $stmt = $pdo->prepare("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :name AND TABLE_NAME = 'migrations'");
        $stmt->execute(['name' => $name]);
        $result = $stmt->fetch();

        if ($result) {
            echo "[Migration] OK Migrations table exists", PHP_EOL;
        } else {
            echo "[Migration] FAILED Migrations table does not exist", PHP_EOL;
            exit(1);
        }

        echo cmd(['migration_start']);
    }
} catch (Exception $e) {
    echo "[Migration] FAILED ", $e->getMessage(), PHP_EOL;
}
