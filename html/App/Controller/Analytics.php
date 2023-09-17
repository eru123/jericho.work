<?php

namespace App\Controller;

use Exception;
use App\Plugin\DB;
use eru123\router\Context;

class Analytics
{
    public static function insertReport(string $type, array $data, int $user_id = 0): string|false
    {
        $date = date('Y-m-d H:i:s');
        $stmt = DB::instance()->query("INSERT INTO `reports` (`type`, `data`, `created_at`) VALUES (?, ?, ?)", [$type, json_encode($data), $date]);
        return DB::instance()->last_insert_id() ?? false;
    }

    public function report(Context $c)
    {
        $raw = $c->json();
        $user_id = intval(@$c->jwt['id']);
        $data = [];
        $required = ['type', 'data'];
        foreach ($required as $key) {
            if (!isset($raw[$key])) {
                throw new Exception("Missing required field $key");
            }
            $data[$key] = $raw[$key];
        }

        if (!static::insertReport($data['type'], $data['data'], $user_id)) {
            throw new Exception("Failed to create report", 500);
        }

        return [
            'success' => "Report success"
        ];
    }
}
