<?php

namespace App\Controller;

use Throwable;
use Exception;
use App\Models\Users;
use App\Models\Tokens;
use App\Plugin\MC;
use App\Plugin\DB;
use eru123\router\Context;
use eru123\helper\JWT;

class Auth
{
    const REVOKED_CACHE_PREFIX = 'revoked_token_';

    public function revoke_token($token): bool
    {
        try {
            $mc = MC::instance();
            $data = static::jwt()->decode($token);
            $exp = @$data['exp'] ?? null;
            $exp = $exp ? date('Y-m-d H:i:s', strtotime($exp)) : null;
            $uid = @$data['id'] ?? 0;
            $data = [
                'token' => $token,
                'user_id' => $uid,
                'type' => 'revoked',
                'expired_at' => $exp,
            ];
            $stmt = Tokens::insert($data);
            if ($stmt->rowCount() > 0) {
                $mc->set(static::REVOKED_CACHE_PREFIX . $token, $exp, strtotime($exp) - time());
                return true;
            }

            throw new Exception('Failed to revoke token');
        } catch (Throwable) {
            return false;
        }
    }

    public function is_revoked($token): bool
    {
        $mc = MC::instance();
        $exp = $mc->get(static::REVOKED_CACHE_PREFIX . $token);
        if ($exp) {
            return true;
        }

        return false;
    }

    public static function store_revoked_tokens()
    {
        $mc = MC::instance();
        $stmt = Tokens::get_all_by_type('revoked');
        while ($row = $stmt->fetch()) {
            $mc->set(static::REVOKED_CACHE_PREFIX . $row['token'], $row['expired_at'], strtotime($row['expired_at']) - time());
        }
    }

    public function bootstrap(Context $c)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                return [
                    'success' => 'Preflight request',
                ];
            }

            $authorization = @getallheaders()['Authorization'] ?? null;
            preg_match('/^(Bearer\s+)?(.+)$/', (string) $authorization, $matches);
            $token = $matches[2] ?? null;

            if (!$token) {
                throw new Exception('Missing Authorization header');
            }

            if ($this->is_revoked($token)) {
                throw new Exception('Token is revoked');
            }

            $c->jwt = static::jwt()->decode($token);
            $c->jwt_error = null;
            $c->jwt_token = $token;
        } catch (Throwable $th) {
            $c->jwt = null;
            $c->jwt_error = $th->getMessage();
            $c->jwt_token = null;
        }
    }

    public function guard(Context $c)
    {
        $msg = "Not authorized";

        if (!$c->jwt) {
            http_response_code(401);

            if (env('APP_ENV') === 'development') {
                $msg .= ': ' . ($c->jwt_error ?? 'Missing Authorization header');
            }

            return [
                'error' => $msg,
            ];
        }

        if (static::is_revoked($c->jwt_token)) {
            http_response_code(401);

            if (env('APP_ENV') === 'development') {
                $msg .= ': Token is revoked';
            }

            return [
                'error' => $msg,
            ];
        }
    }

    public static function jwt(): JWT
    {
        return new JWT(env('JWT_SECRET', 'secret'), 'HS256');
    }

    public function register(Context $c)
    {
        $rdata = $c->json();
        $data = [];
        $required = ['user', 'password', 'fname', 'lname'];
        $allowed = [
            'user',
            'password',
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
        ];

        foreach ($required as $key) {
            if (!isset($rdata[$key]) || empty($rdata[$key])) {
                http_response_code(400);
                return [
                    'error' => "Missing required field: $key",
                ];
            }
        }

        foreach ($allowed as $key) {
            if (isset($rdata[$key])) {
                $data[$key] = $rdata[$key];
            }
        }

        unset($rdata);
        unset($allowed);
        unset($required);

        $stmt = Users::insert($data);
        if ($stmt->rowCount() > 0) {
            try {
                $id = DB::instance()->pdo()->lastInsertId();
                $data = Users::find($id);
                $token = static::create_token($data, '1hr');
            } catch (Throwable $th) {
                return [
                    'error' => 'Registration successful, but failed to create login token, please login manually.',
                ];
            }

            http_response_code(201);
            return [
                'success' => 'Successfully created an account',
                'data' => $data,
                'token' => $token,
            ];
        }

        http_response_code(400);
        return [
            'error' => 'Failed to create an account',
        ];
    }

    public static function create_token(array $data, string $expires = '1d'): string
    {
        $payload = [
            'id' => @$data['id'],
            'user' => @$data['user'],
            'roles' => @$data['roles'],
            'exp' => "now + {$expires}",
            'iat' => 'now',
        ];

        return static::jwt()->encode($payload);
    }

    public function login(Context $c)
    {
        $data = $c->json();
        $required = ['user', 'password'];
        foreach ($required as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                http_response_code(400);
                return [
                    'error' => "Missing required field: $key",
                ];
            }
        }

        $user = Users::login($data['user'], $data['password']);
        if ($user) {
            $token = static::create_token($user, '1hr');
            return [
                'success' => 'Successfully logged in',
                'data' => $user,
                'token' => $token,
            ];
        }

        http_response_code(401);
        return [
            'error' => 'Invalid username or password',
        ];
    }

    public function update(Context $c)
    {
        $rdata = $c->json();
        $user_id = $c->jwt['id'];
        $data = [];

        $allowed = [
            'user',
            'password',
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
        ];

        foreach ($allowed as $key) {
            if (isset($rdata[$key])) {
                $data[$key] = $rdata[$key];
            }
        }

        unset($rdata);
        unset($allowed);

        $stmt = Users::update($user_id, $data);
        if ($stmt->rowCount() > 0) {
            $data = Users::find($user_id);
            return [
                'success' => 'Successfully updated your account',
                'data' => $data,
            ];
        }

        http_response_code(400);
        return [
            'error' => 'Failed to update your account',
        ];
    }

    public function logout(Context $c)
    {
        if ($c->jwt_token) {
            $revoked = $this->revoke_token($c->jwt_token);
            if ($revoked) {
                return [
                    'success' => 'Successfully logged out',
                ];
            }
        }

        http_response_code(400);
        return [
            'error' => 'Failed to logout',
        ];
    }

    public function hello(Context $c)
    {
        $rdata = $c->json();
        $user_id = $c->jwt['id'];

        $renew_token = !!@$rdata['token'];
        $renew_data = !!@$rdata['data'];

        $res = [];

        $user = Users::find($user_id);
        if (!$user) {
            http_response_code(400);
            return [
                'error' => 'Failed to get your account data',
            ];
        }

        if ($renew_data) {
            $res['data'] = $user;
        }

        if ($renew_token) {
            $token = static::create_token($user, '1hr');
            $res['token'] = $token;
            if (!$this->revoke_token($c->jwt_token)) {
                http_response_code(400);
                return [
                    'error' => 'Failed to renew your token',
                ];
            }
            if (!$renew_data) {
                $res['data'] = $user;
            }
        }

        return [
            'success' => "Hi there! here's your updated data",
        ] + $res;
    }

    public function add_email(Context $c)
    {
        // TODO: Add email to user
    }
}
