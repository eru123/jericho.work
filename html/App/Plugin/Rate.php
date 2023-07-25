<?php

namespace App\Plugin;

use DateTime;

class Rate
{
    static $instance = null;
    private $mc = null;

    public static function instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function mc(): MC
    {
        return $this->mc;
    }

    public function __construct()
    {
        $this->mc = MC::instance();
    }

    public static function ip(string $mode, int $limit, string $prefix = '')
    {
        $s = static::instance();
        $d = 'YmdHis';
        $mode = substr($d, 0, strlen($d) - strpos(strrev($d), $mode));
        $date = date($mode);
        $ip = get_ip();
        $identifier = str_replace(['.', ':'], $prefix . '_', $date . "_" . $ip);
        $count = $s->mc()->get($identifier) ?? 0;

        $limit_reach = false;

        if ($count >= $limit) {
            $limit_reach = true;
        }

        $count += 1;

        switch ($mode) {
            case 'Y':
                $s->mc()->set($identifier, $count, 31536000);
                $reset_time = strtotime('+1 year');
                $reset_at = date('Y-01-01 00:00:00', $reset_time);
                break;
            case 'Ymd':
                $s->mc()->set($identifier, $count, 86400);
                $reset_time = strtotime('+1 day');
                $reset_at = date('Y-m-d 00:00:00', $reset_time);
                break;
            case 'YmdH':
                $s->mc()->set($identifier, $count, 3600);
                $reset_time = strtotime('+1 hour');
                $reset_at = date('Y-m-d H:00:00', $reset_time);
                break;
            case 'YmdHi':
                $s->mc()->set($identifier, $count, 60);
                $reset_time = strtotime('+1 minute');
                $reset_at = date('Y-m-d H:i:00', $reset_time);
                break;
            case 'YmdHis':
            default:
                $s->mc()->set($identifier, $count, 1);
                $reset_time = strtotime('+1 second');
                $reset_at = date('Y-m-d H:i:s', $reset_time);
                break;
        }

        $remaining = $limit - $count;
        $reset_ts =  strtotime($reset_at);
        $time_diff = $reset_ts - time();
        $time_diff = $time_diff > 0 ? $time_diff : 0;

        if (!headers_sent()) {
            if ($limit_reach) {
                http_response_code(429);
            }
            header('X-RateLimit-Limit: ' . $limit);
            header('X-RateLimit-Remaining: ' . ($remaining > 0 ? $remaining : 0));
            header('X-RateLimit-Reset: ' . $reset_ts);
            header('Retry-After: ' . $time_diff);
        }

        return $limit_reach;
    }
}
