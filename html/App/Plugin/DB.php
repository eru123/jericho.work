<?php

namespace App\Plugin;

use eru123\orm\Raw;
use PDO;
use PDOStatement;

class DB
{
    static $instance = null;
    private $pdo = null;
    private $stmt = null;
    private $history = [];

    public static function instance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function __construct()
    {
        $host = env('DB_HOST', 'localhost');
        $port = env('DB_PORT', 3306);
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');
        $name = env('DB_NAME', 'main');

        $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $pass , [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function stmt(): PDOStatement
    {
        return $this->stmt;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $sql = Raw::build($sql, $params);

        if (env('APP_ENV') === 'development') {
            $this->history[] = (string) $sql;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $this->stmt = $stmt;
        return $stmt;
    }

    public function queryHistory(): array
    {
        return $this->history;
    }

    public function insert(string $table, array $data = []): PDOStatement
    {
        $keys = array_keys($data);
        $values = array_values($data);
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', array_fill(0, count($values), '?')) . ")";
        return $this->query($sql, $values);
    }

    public function insert_many(string $table, array $data = []): PDOStatement
    {
        $keys = array_keys($data[0]);
        $values = [];
        foreach ($data as $d) {
            $values = array_merge($values, array_values($d));
        }
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES " . implode(', ', array_fill(0, count($data), '(' . implode(', ', array_fill(0, count($keys), '?')) . ')'));
        return $this->query($sql, $values);
    }

    public function last_insert_id(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data = [], array|string $where = null): PDOStatement
    {
        $keys = array_keys($data);
        $values = array_values($data);
        $sql = "UPDATE `$table` SET `" . implode('` = ?, `', $keys) . "` = ?";
        if (is_array($where)) {
            $sql .= " WHERE " . implode(' AND ', array_map(function ($k) {
                return "`$k` = ?";
            }, array_keys($where)));
            $values = array_merge($values, array_values($where));
        } else if (is_string($where)) {
            $sql .= " WHERE $where";
        } else {
            $sql .= " WHERE 1";
        }
        return $this->query($sql, $values);
    }

    public function delete(string $table, array|string  $where = null): PDOStatement
    {
        $sql = "DELETE FROM `$table`";
        $values = [];
        if (is_array($where)) {
            $sql .= " WHERE " . implode(' AND ', array_map(function ($k) {
                return "`$k` = ?";
            }, array_keys($where)));
            $values = array_merge($values, array_values($where));
        } else if (is_string($where)) {
            $sql .= " WHERE $where";
        } else {
            $sql .= " WHERE 1";
        }
        return $this->query($sql, array_values($values));
    }

    public static function build(string $sql, array $params = []): Raw
    {
        return Raw::build($sql, $params);
    }
}
