<?php

namespace App\Models;

use eru123\orm\Raw;
use App\Plugin\DB;
use PDOStatement;

class MailTemplates implements Model
{
    public static function sanitize(array $data): array
    {
        $sanitized = [];
        $allowed = [
            'user_id',
            'code',
            'template',
            'active',
            'default'
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
        return DB::instance()->insert('mail_templates', $data);
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

        return DB::instance()->insert_many('mail_templates', $sanitized);
    }

    public static function update(int|string $id, array $data): PDOStatement
    {
        $data = static::sanitize($data);
        $data['updated_at'] = date('Y-m-d H:i:s');


        if (is_numeric($id)) {
            return DB::instance()->update('mail_templates', $data, ['id' => $id]);
        }

        return DB::instance()->update('mail_templates', $data, $id);
    }

    public static function delete(int|string $id): PDOStatement
    {
        if (is_numeric($id)) {
            return DB::instance()->update('mail_templates', ['deleted_at' => date('Y-m-d H:i:s')], ['id' => $id]);
        }

        return DB::instance()->update('mail_templates', ['deleted_at' => date('Y-m-d H:i:s')], $id);
    }

    public static function deleteUnsafe(int|string $id): PDOStatement
    {
        if (is_numeric($id)) {
            return DB::instance()->delete('mail_templates', ['id' => $id]);
        }

        return DB::instance()->delete('mail_templates', $id);
    }

    public static function purge(int|string $id = null): PDOStatement
    {
        if (is_null($id)) {
            return DB::instance()->delete('mail_templates', '`deleted_at` IS NOT NULL');
        }

        if (is_numeric($id)) {
            return DB::instance()->delete('mail_templates', (string) Raw::build('id = ? AND deleted_at IS NOT NULL', [$id]));
        }

        return DB::instance()->delete('mail_templates', $id);
    }

    public static function find(int|string $id): array|null
    {
        if (is_numeric($id)) {
            return DB::instance()->query('SELECT * FROM `mail_templates` WHERE `id` = ? AND `deleted_at` IS NULL', [$id])->fetch();
        }

        return DB::instance()->query('SELECT * FROM `mail_templates` WHERE `deleted_at` IS NULL AND ' . $id)->fetch();
    }

    public static function find_many(int|string $id): array
    {
        if (is_numeric($id)) {
            return DB::instance()->query('SELECT * FROM `mail_templates` WHERE `id` = ? AND `deleted_at` IS NULL', [$id])->fetchAll();
        }

        return DB::instance()->query('SELECT * FROM `mail_templates` WHERE `deleted_at` IS NULL AND ' . $id)->fetchAll();
    }
}
