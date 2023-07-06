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

    public static function instance()
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
        $this->pdo = new PDO($dsn, $user, $pass);
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $sql = Raw::build($sql, $params);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $this->stmt = $stmt;
        return $stmt;
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

    public function update(string $table, array $data = [], array $where = []): PDOStatement
    {
        $keys = array_keys($data);
        $values = array_values($data);
        $sql = "UPDATE `$table` SET `" . implode('` = ?, `', $keys) . "` = ?";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', array_map(function ($k) {
                return "`$k` = ?";
            }, array_keys($where)));
            $values = array_merge($values, array_values($where));
        }
        return $this->query($sql, $values);
    }

    public function delete(string $table, array $where = []): PDOStatement
    {
        $sql = "DELETE FROM `$table`";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', array_map(function ($k) {
                return "`$k` = ?";
            }, array_keys($where)));
        }
        return $this->query($sql, array_values($where));
    }
}
