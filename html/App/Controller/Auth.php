<?php

namespace App\Controller;

use App\Models\Users;
use App\Plugin\DB;
use eru123\router\Context;
use eru123\helper\JWT;

class Auth
{
    public function register(Context $c)
    {
        $rdata = $c->json();
        $data = [];
        $required = ['user', 'password', 'fname', 'lname'];
        $allowed = [
            'user',
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
            if (!isset($rdata[$key])) {
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
            $id = DB::instance()->pdo()->lastInsertId();
            $data = Users::get($id);
            $token = static::create_token($data, '1hr');

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

        $jwt = new JWT(env('JWT_SECRET', 'secret'), 'HS256');
        return $jwt->encode($payload);
    }

    
}
