<?php

namespace App\Plugin;

use eru123\helper\JWT;
use App\Controller\Auth;
use Exception;

class ACL
{
    static $payload = null;
    static $dec_err = null;
    static $dec_err_code = 0;
    static $arr_uroles = [];
    
    public static function decode_error(): string|null
    {
        return static::$dec_err;
    }

    public static function decode_error_code(): string|null
    {
        return static::$dec_err_code;
    }

    public static function token(): string|null
    {
        $authorization = @getallheaders()['Authorization'] ?? (@getallheaders()['authorization'] ?? null);
        preg_match('/^(Bearer\s+)?(.+)$/', (string) $authorization, $matches);
        return @$matches[2];
    }

    public static function payload(bool $force = false): array|null
    {
        try {
            if (isset(static::$payload) && !$force) {
                return static::$payload;
            }

            $token = static::token();
            if (!$token) {
                return null;
            }
            $jwt = new JWT(env('JWT_SECRET', 'secret'), 'HS256');
            if ((new Auth)->is_revoked($token)) {
                return null;
            }
            static::$dec_err = null;
            static::$dec_err_code = 0;
            static::$payload = $jwt->decode($token);
            return static::$payload;
        } catch (Exception $e) {
            static::$dec_err = $e->getMessage();
            static::$dec_err_code = $e->getCode();
            return null;
        }
    }

    function uroles(): array
    {
        if (!count(static::$arr_uroles)) {
            $uroles = a_get(static::payload(), 'roles', []);
            $uroles = is_array($uroles) ? $uroles : (explode('|', $uroles) ?: [$uroles]);
            $uroles = array_map('strval', $uroles);
            $uroles = array_map('trim', $uroles);
            $uroles = array_map('strtolower', $uroles);
            static::$arr_uroles = array_filter($uroles, fn($urole) => !empty($urole) || !is_string($urole));
        }

        return static::$arr_uroles;
    }

    function allow(array|string $roles = '')
    {
        $access = false;
        $uroles = static::uroles();

        if (!count($uroles)) {
            return false;
        }

        $roles = is_array($roles) ? $roles : (explode('|', $roles) ?: [$roles]);
        $roles = array_map('strval', $roles);
        $roles = array_map('trim', $roles);
        $roles = array_map('strtolower', $roles);
        $roles = array_filter($roles, fn($role) => !empty($role));

        if (count($roles)) {
            foreach ($roles as $role) {
                if (!$access && in_array($role, $uroles)) {
                    $access = true;
                }
            }
        }

        return $access;
    }
}