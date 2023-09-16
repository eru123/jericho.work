<?php

namespace eru123\net;

class Tools
{

    /**
     * Ping a host
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return bool|int false if host is unreachable, otherwise return ping in ms
     */
    public static function ping($host, $port = 80, $timeout = 1)
    {
        $st = microtime(true);
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        $et = microtime(true);
        if (!$fp) {
            return false;
        } else {
            fclose($fp);
            return round((($et - $st) * 1000), 0);
        }
    }

    /**
     * Check if an IP is a valid IPv4
     * @param string $ip
     * @return bool
     */
    public static function is_ipv4(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Check if an IP is a valid IPv6
     * @param string $ip
     * @return bool
     */
    public static function is_ipv6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Get public IP
     * @return string
     */
    public static function get_public_ip(): string
    {
        $ip = file_get_contents('https://api.ipify.org');
        return $ip;
    }

    /**
     * Get ip of remote client
     * @return string|false
     */
    public static function get_client_ip(): string|false
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_FORWARDED_FOR'])[0];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = false;
        }

        return $ip;
    }

    public static function whoami(): array
    {
        $res = [
            'headers' => getallheaders(),
        ];

        if (isset($res['headers']['Content-Type'])) {
            $res['body'] = match ($res['headers']['Content-Type']) {
                'application/json' => json_decode(file_get_contents('php://input'), true),
                'application/x-www-form-urlencoded' => $_POST,
                'multipart/form-data' => $_POST,
                default => $_POST
            };
        }

        $res['query'] = $_GET;
        return $res;
    }
}