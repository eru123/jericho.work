<?php

namespace App\Plugin;

use eru123\orm\Raw;
use PDO;
use PDOStatement;

class DB
{
    const CACHE_PREFIX = 'db_';
    const CACHEABLE_TABLES = [
        'cdn',
        'envs',
        'mails',
        'mail_templates',
        'users',
        'verifications',
        'tokens',
        'reports'
    ];

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
        $this->pdo = new PDO($dsn, $user, $pass, [
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

    public function query(string $sql, array $params = [], bool $cached = false): FakeStmt|PDOStatement
    {
        $sql = Raw::build($sql, $params);

        if (env('APP_ENV') === 'development') {
            $this->history[] = (string) $sql;
        }

        if ($cached) {
            $cache_data = static::get_cached_data($sql);
            if ($cache_data) {
                return $cache_data;
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $this->stmt = $stmt;

        if ($cached) {
            $rows = $stmt->fetchAll();
            $row_count = $stmt->rowCount();
            $data = [
                'rows' => $rows,
                'row_count' => $row_count,
                'table_versions' => static::get_cached_table_versions(),
            ];
            static::create_cached_data($sql, $data);
        }

        return $stmt;
    }

    public function queryHistory(): array
    {
        return $this->history;
    }

    public static function update_cache_table_version(string $table): void
    {
        if (in_array($table, static::CACHEABLE_TABLES)) {
            $mc = MC::instance();
            $identifier = static::CACHE_PREFIX . 'table_version_' . $table;
            $mc->obj()->increment($identifier, 1, 1, 86400);
        }
    }

    public static function get_cached_table_versions(): array
    {
        $versions = [];
        foreach (static::CACHEABLE_TABLES as $table) {
            $mc = MC::instance();
            $identifier = static::CACHE_PREFIX . 'table_version_' . $table;
            $versions[$table] = intval($mc->get($identifier));
        }
        return $versions;
    }

    public static function verify_cache_table_version(array $table_versions): bool
    {
        $versions = static::get_cached_table_versions();
        foreach (static::CACHEABLE_TABLES as $table) {
            if ($versions[$table] !== $table_versions[$table]) {
                return false;
            }
        }
        return true;
    }

    public static function create_cached_data(string $sql, array $data): void
    {
        $mc = MC::instance();
        $identifier = static::CACHE_PREFIX . md5($sql);
        $mc->set($identifier, json_encode($data), 86400);
    }

    public static function get_cached_data(string $sql): FakeStmt|null
    {
        $mc = MC::instance();
        $identifier = static::CACHE_PREFIX . md5($sql);
        $data = $mc->get($identifier);
        if ($data) {
            $data = json_decode($data, true);
            if (static::verify_cache_table_version($data['table_versions'])) {
                return new FakeStmt($data);
            }
            $mc->delete($identifier);
        }
        return null;
    }

    public function insert(string $table, array $data = []): PDOStatement
    {
        $keys = array_keys($data);
        $values = array_values($data);
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', array_fill(0, count($values), '?')) . ")";
        for ($i = 0; $i < count($values); $i++) {
            if (is_array($values[$i])) {
                $values[$i] = count($values[$i]) ? json_encode($values[$i]) : null;
            }
        }
        static::update_cache_table_version($table);
        return $this->query($sql, $values, false);
    }

    public function insert_many(string $table, array $data = []): PDOStatement
    {
        $keys = array_keys($data[0]);
        $values = [];
        foreach ($data as $d) {
            $rowdata = array_values($d);
            for ($i = 0; $i < count($rowdata); $i++) {
                if (is_array($rowdata[$i])) {
                    $rowdata[$i] = count($rowdata[$i]) ? json_encode($rowdata[$i]) : null;
                }
            }
            $values = array_merge($values, $rowdata);
        }
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES " . implode(', ', array_fill(0, count($data), '(' . implode(', ', array_fill(0, count($keys), '?')) . ')'));
        static::update_cache_table_version($table);
        return $this->query($sql, $values, false);
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
        for ($i = 0; $i < count($values); $i++) {
            if (is_array($values[$i])) {
                $values[$i] = count($values[$i]) ? json_encode($values[$i]) : null;
            }
        }
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
        static::update_cache_table_version($table);
        return $this->query($sql, $values, false);
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
        static::update_cache_table_version($table);
        return $this->query($sql, array_values($values), false);
    }

    public static function build(string $sql, array $params = []): Raw
    {
        return Raw::build($sql, $params);
    }
}
