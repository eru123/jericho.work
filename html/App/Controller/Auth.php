<?php

namespace App\Controller;

use Throwable;
use Exception;
use App\Models\Users;
use App\Plugin\DB;
use eru123\router\Context;
use eru123\helper\JWT;

class Auth
{
    public function bootstrap(Context $c)
    {
        try {
            $authorization = @getallheaders()['Authorization'] ?? null;
            preg_match('/^(Bearer\s+)?(.+)$/', (string) $authorization, $matches);
            $token = $matches[2] ?? null;
            if (!$token) {
                throw new Exception('Missing Authorization header');
            }
            $c->jwt = static::jwt()->decode($token);
            $c->jwt_error = null;
        } catch (Throwable $th) {
            $c->jwt = null;
            $c->jwt_error = $th->getMessage();
        }
    }

    public function guard(Context $c)
    {
        if (!$c->jwt) {
            http_response_code(401);
            return [
                'error' => $c->jwt_error ?? 'Missing Authorization header',
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
                $data = Users::get($id);
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

        return [
            'id' => $user_id,
            'success' => 'Successfully updated profile',
            'data' => $data
        ];
    }
}
