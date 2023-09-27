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
        if (self::$created_at || self::$updated_at) {
            $date = date('Y-m-d H:i:s');
            if (self::$created_at) $data[self::$created_at] = $date;
            if (self::$updated_at) $data[self::$updated_at] = $date;
        }   
        
        return DB::instance()->insert(self::$table, $data);
    }
    public static function insert_many(array $data): PDOStatement
    {
        $sanitized = [];
        if (self::$created_at || self::$updated_at) {
            $date = date('Y-m-d H:i:s');
        }
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
        if (self::$updated_at) $data[self::$updated_at] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (is_numeric($id)) {
            return DB::instance()->update(self::$table, $data, ['id' => $id]);
        }

        return DB::instance()->update(self::$table, $data, $id);
    }
}
