<?php

namespace App\Models;

use App\Plugin\DB;
use eru123\orm\Raw;
use Exception;
use PDOStatement;

class Users implements Model
{
    public static function sanitize(array $data): array
    {
        if (isset($data['password'])) {
            $data['hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
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
                if ($data[$key] instanceof Raw || is_string($data[$key]) || is_int($data[$key]) || is_float($data[$key])) {
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

        if (isset($data['fname'])) {
            if (!preg_match('/^[\p{L}\s]+$/u', $data['fname'])) {
                throw new Exception('Invalid first name', 400);
            } else if (!preg_match('/^[\p{L}\s]{2,255}$/', $data['fname'])) {
                throw new Exception('First name must be between 2 to 255 characters', 400);
            }
        }

        if (isset($data['mname'])) {
            if (!preg_match('/^[\p{L}\s]+$/u', $data['mname'])) {
                throw new Exception('Invalid middle name', 400);
            } else if (!preg_match('/^[\p{L}\s]{2,255}$/', $data['mname'])) {
                throw new Exception('Middle name must be between 2 to 255 characters', 400);
            }
        }

        if (isset($data['lname'])) {
            if (!preg_match('/^[\p{L}\s]+$/u', $data['lname'])) {
                throw new Exception('Invalid last name', 400);
            } else if (!preg_match('/^[\p{L}\s]{2,255}$/', $data['lname'])) {
                throw new Exception('Last name must be between 2 to 255 characters', 400);
            }
        }

        if (isset($data['alias'])) {
            if (!preg_match('/^[\p{L}\s,\.]+$/u', $data['alias'])) {
                throw new Exception('Invalid alias', 400);
            } else if (!preg_match('/^[\p{L}\s,\.]{2,255}$/', $data['alias'])) {
                throw new Exception('Alias must be between 2 to 255 characters', 400);
            }
        }

        if (isset($data['user'])) {
            $user = DB::instance()->query('SELECT id FROM users WHERE user = ?', [$data['user']])->fetch();
            if ($user) {
                throw new Exception('Username already exists', 400);
            }
        }

        return static::sanitize($data);
    }

    public static function insert(array $data): PDOStatement
    {
        $sanitized = self::strict_data_check($data);
        $sanitized['created_at'] = date('Y-m-d H:i:s');
        $sanitized['updated_at'] = $sanitized['created_at'];
        return DB::instance()->insert('users', $sanitized);
    }

    public static function insert_many(array $data): PDOStatement
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

    public static function update(int|string $id, array $data): PDOStatement
    {
        static::reuse_password_check($id, $data);
        static::reuse_username_check($id, $data);
        $sanitized = static::strict_data_check($data);
        $sanitized['updated_at'] = date('Y-m-d H:i:s');

        if (is_numeric($id)) {
            return DB::instance()->update('users', $sanitized, ['id' => (int) $id]);
        }

        return DB::instance()->update('users', $sanitized, $id);
    }

    public static function reuse_password_check(int|string $id, array $data): void
    {
        if (isset($data['password'])) {
            $user = null;
            if (is_numeric($id)) {
                $user = DB::instance()->query('SELECT `hash`, `hash_h` FROM `users` WHERE `id` = ?', [(int) $id])->fetch();
            } else {
                $user = DB::instance()->query('SELECT `hash`, `hash_h` FROM `users` WHERE ' . $id)->fetch();
            }

            if ($user) {
                $hashes = json_decode($user['hash_h'], true) ?? [];
                $hash[] = $user['hash'];
                foreach ($hashes as $h) {
                    if (password_verify($data['password'], $h)) {
                        throw new Exception('For security reasons, you cannot reuse your old password', 400);
                    }
                }
            }
        }
    }

    public static function reuse_username_check(int|string $id, array &$data): void
    {
        if (isset($data['user'])) {
            $user = null;
            if (is_numeric($id)) {
                $user = DB::instance()->query('SELECT `user`, `user_h` FROM `users` WHERE `id` = ?', [(int) $id])->fetch();
            } else {
                $user = DB::instance()->query('SELECT `user`, `user_h` FROM `users` WHERE ' . $id)->fetch();
            }

            if ($user) {
                $usernames = json_decode($user['user_h'], true) ?? [];
                if ($user['user'] == $data['user']) {
                    unset($data['user']);
                    return;
                }

                foreach ($usernames as $u) {
                    if ($u == $data['user']) {
                        throw new Exception('Cannot reuse username', 400);
                    }
                }
            }
        }
    }

    public static function delete(int|string $id): PDOStatement
    {
        if (is_numeric($id)) {
            return DB::instance()->update('users', ['deleted_at' => date('Y-m-d H:i:s')], ['id' => (int) $id]);
        }

        return DB::instance()->update('users', ['deleted_at' => date('Y-m-d H:i:s')], $id);
    }

    public static function deleteUnsafe(int|string $id): PDOStatement
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
        $sql = "SELECT COUNT(*) FROM `users` WHERE (`email` = ? OR JSON_CONTAINS(`emails`, ?)) AND `email_verified` = 1 AND `deleted_at` IS NULL";
        $stmt = DB::instance()->query($sql, [$email, json_encode([$email])]);
        return $stmt->fetchColumn() > 0;
    }

    public static function mobile_exists(string $mobile): bool
    {
        $data = static::strict_data_check(['mobile' => $mobile]);
        $mobile = $data['mobile'];
        $sql = "SELECT COUNT(*) FROM `users` WHERE (`mobile` = ? OR JSON_CONTAINS(`mobiles`, ?)) AND `mobile_verified` = 1 AND `deleted_at` IS NULL";
        $stmt = DB::instance()->query($sql, [$mobile, json_encode([$mobile])]);
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
        $where = [
            'email' => $user,
            'mobile' => $user,
            'id' => $user,
        ];

        preg_match('/^((\+)?(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)|0)?(\d{10,14})$/', $where['mobile'], $matches);

        if (count($matches) > 0) {
            $country_code = '+63';
            $where['mobile'] = $matches[4];
            $has_i_code = $matches[2] ==  '+';
            $number = $matches[4];

            if ($has_i_code) {
                $country_code = $matches[1];
            }
            $where['mobile'] = $country_code . $number;
        } else {
            unset($where['mobile']);
        }

        if (!filter_var($where['email'], FILTER_VALIDATE_EMAIL)) {
            unset($where['email']);
        }

        if (!is_numeric($where['id'])) {
            unset($where['id']);
        }

        $values = [$user];
        $where_query = '`user` = ?';

        if (isset($where['email'])) {
            $where_query .= ' OR (`email` = ? AND `email_verified` = 1)';
            $values[] = $where['email'];
        }

        if (isset($where['mobile'])) {
            $where_query .= ' OR (`mobile` = ? AND `mobile_verified` = 1)';
            $values[] = $where['mobile'];
        }

        if (isset($where['id'])) {
            $where_query .= ' OR `id` = ?';
            $values[] = $where['id'];
        }

        $sql = "SELECT * FROM `users` WHERE ({$where_query}) AND `deleted_at` IS NULL";
        $stmt = DB::instance()->query($sql, $values);
        $user = $stmt->fetch();
        if ($user) {
            if (password_verify($pass, $user['hash'])) {
                unset($user['hash']);
                unset($user['hash_h']);
                $user['roles'] = json_decode(@$user['roles'] ?? 'null');
                $user['addresses'] = json_decode(@$user['addresses'] ?? 'null');
                $user['emails'] = json_decode(@$user['emails'] ?? 'null');
                $user['mobiles'] = json_decode(@$user['mobiles'] ?? 'null');
                $user['providers'] = json_decode(@$user['providers'] ?? 'null');
                $user['mobile_verified'] = $user['mobile_verified'] == 1;
                $user['email_verified'] = $user['email_verified'] == 1;
                if (!empty($user['alias'])) {
                    $user['name'] = $user['alias'];
                } else {
                    $nl = ['pronoun', 'fname', 'mname', 'lname', 'suffix'];
                    $na = [];
                    foreach ($nl as $n) {
                        if (isset($user[$n]) && !empty($user[$n])) {
                            $na[] = $user[$n];
                        }
                    }
                    $user['name'] = count($na) ? implode(' ', $na) : 'User';
                }
                return $user;
            }
        }

        return null;
    }

    public static function purge(int|string $id = null): PDOStatement
    {
        if (is_null($id)) {
            return DB::instance()->delete('users', '`deleted_at` IS NOT NULL');
        }

        if (is_numeric($id)) {
            return DB::instance()->delete('users', (string) Raw::build('id = ? AND deleted_at IS NOT NULL', [$id]));
        }

        return DB::instance()->delete('users', $id);
    }

    public static function find(int|string $id): array|null|false
    {
        $res = null;
        if (is_numeric($id)) {
            $sql = "SELECT * FROM `users` WHERE `id` = ? AND `deleted_at` IS NULL";
            $stmt = DB::instance()->query($sql, [$id]);
            $res = $stmt->fetch();
        } else {
            $sql = "SELECT * FROM `users` WHERE `deleted_at` IS NULL AND {$id}";
            $stmt = DB::instance()->query($sql);
            $res = $stmt->fetch();
        }

        if ($res) {
            unset($res['hash']);
            unset($res['hash_h']);
            $res['roles'] = json_decode(@$res['roles'] ?? 'null');
            $res['addresses'] = json_decode(@$res['addresses'] ?? 'null');
            $res['emails'] = json_decode(@$res['emails'] ?? 'null');
            $res['mobiles'] = json_decode(@$res['mobiles'] ?? 'null');
            $res['providers'] = json_decode(@$res['providers'] ?? 'null');
            $res['mobile_verified'] = $res['mobile_verified'] == 1;
            $res['email_verified'] = $res['email_verified'] == 1;
            if (!empty($res['alias'])) {
                $res['name'] = $res['alias'];
            } else {
                $nl = ['pronoun', 'fname', 'mname', 'lname', 'suffix'];
                $na = [];
                foreach ($nl as $n) {
                    if (isset($res[$n]) && !empty($res[$n])) {
                        $na[] = $res[$n];
                    }
                }
                $res['name'] = count($na) ? implode(' ', $na) : 'User';
            }
        }

        return $res;
    }

    public static function find_many(int|string $id): array
    {
        $res = null;
        if (is_numeric($id)) {
            $sql = "SELECT * FROM `users` WHERE `id` = ? AND `deleted_at` IS NULL";
            $stmt = DB::instance()->query($sql, [$id]);
            $res = $stmt->fetchAll();
        } else {
            $sql = "SELECT * FROM `users` WHERE `deleted_at` IS NULL AND {$id}";
            $stmt = DB::instance()->query($sql);
            $res = $stmt->fetchAll();
        }

        if ($res) {
            foreach ($res as &$r) {
                unset($r['hash']);
                unset($r['hash_h']);
                $r['roles'] = json_decode(@$r['roles'] ?? 'null');
                $r['addresses'] = json_decode(@$r['addresses'] ?? 'null');
                $r['emails'] = json_decode(@$r['emails'] ?? 'null');
                $r['mobiles'] = json_decode(@$r['mobiles'] ?? 'null');
                $r['providers'] = json_decode(@$r['providers'] ?? 'null');
                $r['mobile_verified'] = $r['mobile_verified'] == 1;
                $r['email_verified'] = $r['email_verified'] == 1;
                if (!empty($r['alias'])) {
                    $r['name'] = $r['alias'];
                } else {
                    $nl = ['pronoun', 'fname', 'mname', 'lname', 'suffix'];
                    $na = [];
                    foreach ($nl as $n) {
                        if (isset($r[$n]) && !empty($r[$n])) {
                            $na[] = $r[$n];
                        }
                    }
                    $r['name'] = count($na) ? implode(' ', $na) : 'User';
                }
            }
        }

        return $res;
    }
}
