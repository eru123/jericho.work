<?php

namespace App\Models;

use PDOStatement;
use eru123\orm\Raw;
use App\Plugin\DB;

abstract class AbstractModel implements Model
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

        foreach (static::$allowed as $key) {
            if (isset($data[$key])) {
                $sanitized[$key] = $data[$key];
            }
        }

        return $sanitized;
    }

    public static function insert(array $data): PDOStatement
    {
        $data = static::sanitize($data);
        $date = static::$created_at || static::$updated_at ? date(static::$date_format) : null;
        if (static::$created_at) $data[static::$created_at] = $date;
        if (static::$updated_at) $data[static::$updated_at] = $date;
        return DB::instance()->insert(static::$table, $data);
    }

    public static function insert_many(array $data): PDOStatement
    {
        $sanitized = [];
        $date = static::$created_at || static::$updated_at ? date(static::$date_format) : null;
        foreach ($data as $row) {
            $tmp = static::sanitize($row);
            if (static::$created_at) $tmp[static::$created_at] = $date;
            if (static::$updated_at) $tmp[static::$updated_at] = $date;
            $sanitized[] = $tmp;
        }

        return DB::instance()->insert_many(static::$table, $sanitized);
    }

    public static function update(int|string $id, array $data): PDOStatement
    {
        $data = static::sanitize($data);
        $where = [];
        $values = [];

        if (static::$soft_delete && static::$deleted_at) {
            $where[] = '`' . static::$deleted_at . '` IS NULL';
        }

        if (static::$updated_at && !isset($data[static::$updated_at])) {
            $data[static::$updated_at] = date(static::$date_format);
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && static::$primary_key) {
            $where[] = '`' . static::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->update(static::$table, $data, $where);
    }

    public static function delete(int|string $id): PDOStatement
    {
        $where = [];
        $values = [];
        $data = [];

        if (static::$soft_delete && static::$deleted_at) {
            $where[] = '`' . static::$deleted_at . '` IS NULL';
            $data[static::$deleted_at] = date(static::$date_format);
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && static::$primary_key) {
            $where[] = '`' . static::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }
        
        $where = Raw::build(implode(' AND ', $where), $values);
        
        if (static::$soft_delete && static::$deleted_at) {
            return DB::instance()->update(static::$table, $data, $where);
        }

        return DB::instance()->query('DELETE FROM `'.static::$table.'` WHERE ' . $where);
    }

    public static function deleteUnsafe(int|string $id): PDOStatement
    {
        $where = [];
        $values = [];

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && static::$primary_key) {
            $where[] = '`' . static::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->query('DELETE FROM `'.static::$table.'` WHERE ' . $where);
    }

    public static function purge(int|string $id = null): PDOStatement
    {
        $where = [];
        $values = [];

        if (static::$soft_delete && static::$deleted_at) {
            $where[] = '`' . static::$deleted_at . '` IS NOT NULL';
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && static::$primary_key) {
            $where[] = '`' . static::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->query('DELETE FROM `'.static::$table.'` WHERE ' . $where);
    }

    public static function find(int|string $id): array|null|false
    {
        $where = [];
        $values = [];

        if (static::$soft_delete && static::$deleted_at) {
            $where[] = '`' . static::$deleted_at . '` IS NULL';
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && static::$primary_key) {
            $where[] = '`' . static::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->query('SELECT * FROM `'.static::$table.'` WHERE ' . $where . ' LIMIT 1')->fetch();
    }

    public static function find_many(int|string $id): array
    {
        $where = [];
        $values = [];

        if (static::$soft_delete && static::$deleted_at) {
            $where[] = '`' . static::$deleted_at . '` IS NULL';
        }

        if (empty($id)) {
            $where[] = '1';
        } else if (is_numeric($id) && static::$primary_key) {
            $where[] = '`' . static::$primary_key . '` = ?';
            $values[] = $id;
        } else {
            $where[] = $id;
        }

        $where = Raw::build(implode(' AND ', $where), $values);
        return DB::instance()->query('SELECT * FROM `'.static::$table.'` WHERE ' . $where)->fetchAll();
    }
}
