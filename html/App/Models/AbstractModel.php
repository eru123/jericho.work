<?php

namespace App\Models;

use PDOStatement;
use eru123\orm\Raw;
use App\Plugin\DB;

abstract class AbstractModel implements Newsletter
{   
    protected static $table = '';
    protected static $allowed = [];
    protected static $created_at = false;
    protected static $updated_at = false;
    protected static $deleted_at = false;
    protected static $disabled_at = false;
    protected static $primary_key = false;

    protected static $date_format = 'Y-m-d H:i:s';
    protected static $soft_delete = false;
    protected static $use_created_at = false;
    protected static $use_updated_at = false;

    public static function sanitize(array $data): array
    {
        $sanitized = [];

        foreach (self::$allowed as $key) {
            if (isset($data[$key])) {
                $sanitized[$key] = $data[$key];
            }
        }

        return $sanitized;
    }

    public static function insert(array $data): PDOStatement
    {
        $data = static::sanitize($data);
        $date = self::$created_at || self::$updated_at ? date(self::$date_format) : null;
        if (self::$created_at) $data[self::$created_at] = $date;
        if (self::$updated_at) $data[self::$updated_at] = $date;
        return DB::instance()->insert(self::$table, $data);
    }

    public static function insert_many(array $data): PDOStatement
    {
        $sanitized = [];
        $date = self::$created_at || self::$updated_at ? date(self::$date_format) : null;
        foreach ($data as $row) {
            $tmp = static::sanitize($row);
            if (self::$created_at) $tmp[self::$created_at] = $date;
            if (self::$updated_at) $tmp[self::$updated_at] = $date;
            $sanitized[] = $tmp;
        }

        return DB::instance()->insert_many(self::$table, $sanitized);
    }

    public static function update(int|string $id, array $data): PDOStatement
    {
        $data = static::sanitize($data);
        $where = [];
        $values = [];

        if (self::$soft_delete && self::$deleted_at) {
            $where[] = '`' . self::$deleted_at . '` IS NOT NULL';
        }

        if (self::$updated_at && !isset($data[self::$updated_at])) {
            $data[self::$updated_at] = date(self::$date_format);
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && self::$primary_key) {
            $where[] = '`' . self::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->update(self::$table, $data, $where);
    }

    public static function delete(int|string $id): PDOStatement
    {
        $where = [];
        $values = [];
        $data = [];

        if (self::$soft_delete && self::$deleted_at) {
            $where[] = '`' . self::$deleted_at . '` IS NULL';
            $data[self::$deleted_at] = date(self::$date_format);
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && self::$primary_key) {
            $where[] = '`' . self::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }
        
        $where = Raw::build(implode(' AND ', $where), $values);
        
        if (self::$soft_delete && self::$deleted_at) {
            return DB::instance()->update(self::$table, $data, $where);
        }

        return DB::instance()->query('DELETE FROM `'.self::$table.'` WHERE ' . $where);
    }

    public static function deleteUnsafe(int|string $id): PDOStatement
    {
        $where = [];
        $values = [];

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && self::$primary_key) {
            $where[] = '`' . self::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->query('DELETE FROM `'.self::$table.'` WHERE ' . $where);
    }

    public static function purge(int|string $id = null): PDOStatement
    {
        $where = [];
        $values = [];

        if (self::$soft_delete && self::$deleted_at) {
            $where[] = '`' . self::$deleted_at . '` IS NOT NULL';
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && self::$primary_key) {
            $where[] = '`' . self::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->query('DELETE FROM `'.self::$table.'` WHERE ' . $where);
    }

    public static function find(int|string $id): array|null|false
    {
        $where = [];
        $values = [];

        if (self::$soft_delete && self::$deleted_at) {
            $where[] = '`' . self::$deleted_at . '` IS NULL';
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && self::$primary_key) {
            $where[] = '`' . self::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->query('SELECT * FROM `'.self::$table.'` WHERE ' . $where . ' LIMIT 1')->fetch();
    }

    public static function find_many(int|string $id): array
    {
        $where = [];
        $values = [];

        if (self::$soft_delete && self::$deleted_at) {
            $where[] = '`' . self::$deleted_at . '` IS NULL';
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && self::$primary_key) {
            $where[] = '`' . self::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->query('SELECT * FROM `'.self::$table.'` WHERE ' . $where)->fetchAll();
    }
}
