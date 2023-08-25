<?php

require_once __DIR__ . '/autoload.php';

try {
    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', 3306);
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');
    $name = env('DB_NAME', 'main');

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "[Migration] OK Executing migrations", PHP_EOL;
    $files = glob(__MIGRATIONS__ . DIRECTORY_SEPARATOR . '*.sql');
    $sanitized_files = [];

    foreach ($files as $file) {
        $file = realpath($file);
        if (!$file || !is_file($file)) {
            echo "[Migration] FAILED $file does not exist", PHP_EOL;
        }
        if (!is_readable($file)) {
            echo "[Migration] FAILED $file is not readable", PHP_EOL;
        }
        $sanitized_files[] = $file;
    }

    $basenames = array_map(fn ($file) => basename($file), $sanitized_files);
    $placeholders = array_map(fn () => '?', $basenames);

    $sql = "SELECT * FROM `migrations` WHERE `name` NOT IN (" . implode(", ", $placeholders) . ")";
    $stmt = $pdo->prepare("SELECT * FROM `migrations` WHERE `name` IN (" . implode(", ", $placeholders) . ")");
    $stmt->execute($basenames);
    $result = $stmt->fetchAll();

    if (is_array($result)) {
        foreach ($result as $row) {
            $key = array_search($row['name'], $basenames);
            if ($key !== false) {
                unset($sanitized_files[$key]);
            }
        }
    }

    usort($sanitized_files, function ($a, $b) {
        $a = explode('_', basename($a));
        $b = explode('_', basename($b));
        $a = (int) $a[0];
        $b = (int) $b[0];
        return $a - $b;
    });

    try {
        foreach ($sanitized_files as $file) {
            $sql = file_get_contents($file);
            $pdo->exec($sql);

            echo "[Migration] OK Executed $file", PHP_EOL;
            $stmt = $pdo->prepare("INSERT INTO `migrations` (`type`, `name`) VALUES ('migration', ?)");
            $stmt->execute([basename($file)]);
            $stmt->closeCursor();
        }
        echo "[Migration] OK Migration done", PHP_EOL;
    } catch (Throwable $e) {
        echo "[Migration] FAILED Migrations: ", $e->getMessage(), PHP_EOL;
    }
} catch (Throwable $e) {
    echo "[Migration] FAILED ", $e->getMessage(), PHP_EOL;
}
