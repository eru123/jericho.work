<?php

namespace App\Models;

use eru123\orm\Raw;
use App\Plugin\DB;
use PDOStatement;

class Mails implements Model
{
    public static function sanitize(array $data): array
    {
        $sanitized = [];
        $allowed = [
            'parent_id',
            'user_id',
            'sender_id',
            'message_id',
            'type',
            'subject',
            'to',
            'cc',
            'bcc',
            'body',
            'attachments',
            'priority',
            'meta',
            'status',
            'response',
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
        $date = date('Y-m-d H:i:s');
        $data['created_at'] = $date;
        $data['updated_at'] = $date;
        return DB::instance()->insert('mails', $data);
    }

    public static function insert_many(array $data): PDOStatement
    {
        $sanitized = [];
        $date = date('Y-m-d H:i:s');
        foreach ($data as $row) {
            $tmp = static::sanitize($row);
            $tmp['created_at'] = $date;
            $tmp['updated_at'] = $date;
            $sanitized[] = $tmp;
        }

        return DB::instance()->insert_many('mails', $sanitized);
    }

    public static function update(int|string $id, array $data): PDOStatement
    {
        $data = static::sanitize($data);
        $data['updated_at'] = date('Y-m-d H:i:s');


        if (is_numeric($id)) {
            return DB::instance()->update('mails', $data, ['id' => $id]);
        }

        return DB::instance()->update('mails', $data, $id);
    }

    public static function delete(int|string $id): PDOStatement
    {
        if (is_numeric($id)) {
            return DB::instance()->update('mails', ['deleted_at' => date('Y-m-d H:i:s')], ['id' => $id]);
        }

        return DB::instance()->update('mails', ['deleted_at' => date('Y-m-d H:i:s')], $id);
    }

    public static function deleteUnsafe(int|string $id): PDOStatement
    {
        if (is_numeric($id)) {
            return DB::instance()->delete('mails', ['id' => $id]);
        }

        return DB::instance()->delete('mails', $id);
    }

    public static function purge(int|string $id = null): PDOStatement
    {
        if (is_null($id)) {
            return DB::instance()->delete('mails', '`deleted_at` IS NOT NULL');
        }

        if (is_numeric($id)) {
            return DB::instance()->delete('mails', (string) Raw::build('id = ? AND deleted_at IS NOT NULL', [$id]));
        }

        return DB::instance()->delete('mails', $id);
    }

    public static function find(int|string $id): array|null
    {
        if (is_numeric($id)) {
            return DB::instance()->query('SELECT * FROM `mails` WHERE `id` = ? AND `deleted_at` IS NULL', [$id])->fetch();
        }

        return DB::instance()->query('SELECT * FROM `mails` WHERE `deleted_at` IS NULL AND ' . $id)->fetch();
    }

    public static function find_many(int|string $id): array
    {
        if (is_numeric($id)) {
            return DB::instance()->query('SELECT * FROM `mails` WHERE `id` = ? AND `deleted_at` IS NULL', [$id])->fetchAll();
        }

        return DB::instance()->query('SELECT * FROM `mails` WHERE `deleted_at` IS NULL AND ' . $id)->fetchAll();
    }
}
