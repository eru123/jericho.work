<?php

namespace App\Models;

use App\Plugin\DB;
use PDOStatement;

class Tokens implements Model
{
    public static function sanitize(array $data): array
    {
        $sanitized = [];
        $allowed = [
            'token',
            'user_id',
            'type',
            'expired_at',
        ];

        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                $sanitized[$key] = $data[$key];
            }
        }

        return $sanitized;
    }

    public static function insert(array $data): PDOStatement
    {
        $data = static::sanitize($data);
        return DB::instance()->insert('tokens', $data);
    }

    public static function insert_many(array $data): PDOStatement
    {
        $sanitized = [];
        foreach ($data as $row) {
            $tmp = static::sanitize($row);
            $sanitized[] = $tmp;
        }

        return DB::instance()->insert_many('tokens', $sanitized);
    }

    public static function update(int|string $id, array $data): PDOStatement
    {
        $data = static::sanitize($data);
        return DB::instance()->update('tokens', $data, ['token' => $id]);
    }

    public static function delete(int|string $id): PDOStatement
    {
        return static::deleteUnsafe($id);
    }

    public static function deleteUnsafe(int|string $id): PDOStatement
    {
        return DB::instance()->delete('tokens', ['token' => $id]);
    }

    public static function purge(int|string $id = null): PDOStatement
    {
        return DB::instance()->delete('tokens', '`expired_at` < NOW()');
    }

    public static function find(int|string $id): array|null|false
    {
        return DB::instance()->query('SELECT * FROM `tokens` WHERE `token` = ?', [$id])->fetch();
    }

    public static function find_many(int|string $id): array
    {
        return DB::instance()->query('SELECT * FROM `tokens` WHERE `token` = ?', [$id])->fetchAll();
    }
}
