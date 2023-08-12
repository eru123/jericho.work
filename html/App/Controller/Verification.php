<?php

namespace App\Controller;

use App\Plugin\DB;
use eru123\orm\Raw;
use PDOStatement;
use App\Models\Verifications;

class Verification
{
    public static function generate_code(int $len = 6): string
    {
        return substr(str_shuffle(str_repeat('0123456789', $len)), 0, $len);
    }

    public static function add_mail(string $user_id, string $email)
    {
        $code = static::generate_code(6);
        $data = [
            'user_id' => $user_id,
            'type' => 'email',
            'identifier' => $email,
            'code' => $code,
            'action' => 'add_mail',
            'status' => false,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ];
        Verifications::insert($data);
        return $code;
    }
}
