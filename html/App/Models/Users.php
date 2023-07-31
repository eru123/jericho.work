<?php

namespace App\Models;

use App\Plugin\DB;
use eru123\orm\Raw;
use Exception;

class Users
{
    public static function sanitize(array $data)
    {
        if (isset($data['password'])) {
            $data['hash'] = password_hash($data['password'], PASSWORD_BCRYPT_DEFAULT_COST);
            unset($data['password']);
        }

        $allowed = [
            'user',
            'hash',
            'roles',
            'alias',
            'fname',
            'mname',
            'lname',
            'pronoun',
            'avatar',
            'country',
            'city',
            'state',
            'zip',
            'address',
            'addresses',
            'email',
            'email_verified',
            'emails',
            'mobile',
            'mobile_verified',
            'mobiles',
            'providers',
            'default_smtp',
            'hash_h',
            'user_h',
            'alias_h',
            'created_at',
            'updated_at',
            'deleted_at'
        ];

        $sanitized = [];
        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                if ($data[$key] instanceof Raw || is_string($data[$key]) || is_numeric($data[$key])) {
                    $sanitized[$key] = $data[$key];
                } else if (is_array($data[$key]) || is_object($data[$key])) {
                    $sanitized[$key] = json_encode($data[$key]);
                } else if (is_bool($data[$key])) {
                    $sanitized[$key] = $data[$key] ? 1 : 0;
                } else if (is_null($data[$key])) {
                    $sanitized[$key] = null;
                }
            }
        }

        return $sanitized;
    }

    public static function strict_data_check(array $data)
    {
        if (isset($data['mobile'])) {
            preg_match('/^((\+)?(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)|0)?(\d{10,14})$/', $data['mobile'], $matches);

            if (count($matches) > 0) {
                $country_code = '+63';
                $data['mobile'] = $matches[4];
                $has_i_code = $matches[2] ==  '+';
                $number = $matches[4];

                if ($has_i_code) {
                    $country_code = $matches[1];
                }
                $data['mobile'] = $country_code . $number;
            } else {
                throw new Exception('Invalid mobile number', 400);
            }
        }

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address', 400);
            }
        }

        if (isset($data['user'])) {
            if (strlen($data['user']) < 6) {
                throw new Exception('Username must be atleast 6 characters', 400);
            }
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['user'])) {
                throw new Exception('Username must only contain a-z, A-Z, 0-9, _ and -', 400);
            }
        }

        if (isset($data['password'])) {
            if (strlen($data['password']) < 8) {
                throw new Exception('Password must be atleast 8 characters', 400);
            }

            if (!preg_match('/[A-Z]/', $data['password'])) {
                throw new Exception('Password must contain atleast 1 uppercase letter', 400);
            }

            if (!preg_match('/[a-z]/', $data['password'])) {
                throw new Exception('Password must contain atleast 1 lowercase letter', 400);
            }

            if (!preg_match('/[0-9]/', $data['password'])) {
                throw new Exception('Password must contain atleast 1 number', 400);
            }

            if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $data['password'])) {
                throw new Exception('Password must contain atleast 1 special character', 400);
            }
        }

        return static::sanitize($data);
    }

    public static function insert(array $data)
    {
        $sanitized = self::strict_data_check($data);
        $sanitized['created_at'] = date('Y-m-d H:i:s');
        $sanitized['updated_at'] = $sanitized['created_at'];
        return DB::instance()->insert('users', $sanitized);
    }

    public static function insert_many(array $data)
    {
        $now = date('Y-m-d H:i:s');
        $sanitized = array_map(function ($item) use ($now) {
            return self::strict_data_check($item) + [
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $data);
        return DB::instance()->insert_many('users', $sanitized);
    }

    public static function update(int|string $id, array $data)
    {
        $sanitized = self::strict_data_check($data);
        $sanitized['updated_at'] = date('Y-m-d H:i:s');

        if (is_numeric($id)) {
            return DB::instance()->update('users', $sanitized, ['id' => (int) $id]);
        }

        return DB::instance()->update('users', $sanitized, $id);
    }

    public static function delete(int|string $id)
    {
        if (is_numeric($id)) {
            return DB::instance()->delete('users', ['id' => (int) $id]);
        }

        return DB::instance()->delete('users', $id);
    }

    public static function email_exists(string $email): bool
    {
        $data = static::strict_data_check(['email' => $email]);
        $email = $data['email'];
        $sql = "SELECT COUNT(*) FROM `users` WHERE `email` = ? AND `email_verified` = 1 AND `deleted_at` IS NULL";
        $stmt = DB::instance()->query($sql, [$email]);
        return $stmt->fetchColumn() > 0;
    }

    public static function mobile_exists(string $mobile): bool
    {
        $data = static::strict_data_check(['mobile' => $mobile]);
        $mobile = $data['mobile'];
        $sql = "SELECT COUNT(*) FROM `users` WHERE `mobile` = ? AND `mobile_verified` = 1 AND `deleted_at` IS NULL";
        $stmt = DB::instance()->query($sql, [$mobile]);
        return $stmt->fetchColumn() > 0;
    }

    public static function user_exists(string $user): bool
    {
        $sql = "SELECT COUNT(*) FROM `users` WHERE `user` = ? AND `deleted_at` IS NULL";
        $stmt = DB::instance()->query($sql, [$user]);
        return $stmt->fetchColumn() > 0;
    }

    public static function login(string $user, string $pass): array|null
    {
        $sql = "SELECT * FROM `users` WHERE `user` = ? OR `email` = ? OR `mobile` = ? AND `deleted_at` IS NULL";
        $stmt = DB::instance()->query($sql, [$user, $user, $user]);
        $user = $stmt->fetch();
        if ($user) {
            if (password_verify($pass, $user['hash'])) {
                return $user;
            }
        }

        return null;
    }

    public static function get(int|string $id): array|null
    {
        $res = null;
        if (is_numeric($id)) {
            $sql = "SELECT * FROM `users` WHERE `id` = ? AND `deleted_at` IS NULL";
            $stmt = DB::instance()->query($sql, [$id]);
            $res = $stmt->fetch();
        } else {
            $sql = "SELECT * FROM `users` WHERE `user` = ? AND `deleted_at` IS NULL";
            $stmt = DB::instance()->query($sql, [$id]);
            $res = $stmt->fetch();
        }

        if ($res) {
            unset($res['hash']);
            $res['roles'] = json_decode($res['roles']);
            $res['addresses'] = json_decode($res['addresses']);
            $res['emails'] = json_decode($res['emails']);
            $res['mobiles'] = json_decode($res['mobiles']);
            $res['providers'] = json_decode($res['providers']);
            $res['mobile_verified'] = $res['mobile_verified'] == 1;
            $res['email_verified'] = $res['email_verified'] == 1;
        }

        return $res;
    }
}
