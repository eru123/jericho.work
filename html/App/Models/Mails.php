<?php

namespace App\Models;

use eru123\orm\Raw;
use App\Plugin\DB;
use PDOStatement;

class Mails implements Model
{
    const PRIORITY_NONE = 0;
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;
    const TYPE_TRANSACTIONAL = 'transactional';
    const TYPE_MARKETING = 'marketing';
    const TYPE_AUTORESPONDER = 'autoresponder';
    const STATUS_QUEUE = 1;
    const STATUS_SENT = 2;
    const STATUS_FAILED = 3;

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

    public static function get_queues(): array
    {
        $sender_ids_raw = DB::instance()->query('SELECT DISTINCT `sender_id` FROM `mails` WHERE `status` = ? AND `deleted_at` IS NULL ORDER BY `sender_id` ASC', [static::STATUS_QUEUE])->fetchAll();
        $sender_ids = array_column($sender_ids_raw, 'sender_id');
        unset($sender_ids_raw);
        $has_sys = count($sender_ids) ? $sender_ids[0] == 0 : false;
        if ($has_sys) {
            $sender_ids = array_slice($sender_ids, 1);
        }

        $senders = [];
        if (count($sender_ids)) {
            $senders_raw = DB::instance()->query('SELECT * FROM `smtps` WHERE `id` IN ? AND `deleted_at` IS NULL', [$sender_ids])->fetchAll();

            foreach ($senders_raw as $row) {
                $senders[(string) $row['id']] = $row;
            }
            unset($senders_raw);
        }

        $mail_limits = [];
        if ($has_sys) {
            $mail_limits['0'] = (int) env('SMTP_LIMIT_PER_SECOND', 14);
        }
        foreach ($senders as $row) {
            $mail_limits[(string) $row['id']] = (int) $row['limit'];
        }

        $mails = [];
        foreach ($mail_limits as $sender_id => $limit) {
            $mails_raw = DB::instance()->query('SELECT * FROM `mails` WHERE `sender_id` = ? AND `status` = ? AND `deleted_at` IS NULL ORDER BY `priority` DESC, `id` ASC LIMIT ?', [$sender_id, static::STATUS_QUEUE, $limit])->fetchAll();
            foreach ($mails_raw as $row) {
                $row['to'] = (is_string($row['to']) ? json_decode($row['to'], true) : $row['to']) ?? [];
                $row['cc'] = (is_string($row['cc']) ? json_decode($row['cc'], true) : $row['cc']) ?? [];
                $row['bcc'] = (is_string($row['bcc']) ? json_decode($row['bcc'], true) : $row['bcc']) ?? [];
                $row['attachments'] = (is_string($row['attachments']) ? json_decode($row['attachments'], true) : $row['attachments']) ?? [];
                $row['meta'] = (is_string($row['meta']) ? json_decode($row['meta'], true) : $row['meta']) ?? [];
                $row['response'] = (is_string($row['response']) ? json_decode($row['response'], true) : $row['response']) ?? [];
                $mails[] = $row;
            }
        }

        usort($mails, function ($a, $b) {
            if ($a['priority'] == $b['priority']) {
                return $a['id'] - $b['id'];
            }

            return $a['priority'] - $b['priority'];
        });

        return [
            'senders' => $senders,
            'mails' => $mails,
        ];
    }
}
